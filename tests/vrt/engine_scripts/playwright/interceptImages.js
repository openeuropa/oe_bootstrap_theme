module.exports = async (page) => {
  await page.waitForLoadState("networkidle", { timeout: 30_000 }).catch(() => {});

  // Replace demo images (picsum) with deterministic SVG placeholders.
  await page.evaluate(() => {
    const DEMO_HOSTS = new Set(["picsum.photos", "picsum.dev"]);

    const isDemoUrl = (url) => {
      try {
        const u = new URL(url, window.location.href);
        return DEMO_HOSTS.has(u.host);
      } catch {
        return false;
      }
    };

    const svgDataUri = (w, h) => {
      const bg = "#6b3fa0"; // purple
      const fg = "#2ecc71"; // green
      // Crisp edges helps reduce antialiasing diffs.
      const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}" shape-rendering="crispEdges">
          <rect width="${w}" height="${h}" fill="${bg}"/>
          <rect x="1" y="1" width="${Math.max(0, w - 2)}" height="${Math.max(
        0,
        h - 2
      )}" fill="none" stroke="${fg}" stroke-width="2"/>
          <line x1="0" y1="0" x2="${w}" y2="${h}" stroke="${fg}" stroke-width="2"/>
          <line x1="${w}" y1="0" x2="0" y2="${h}" stroke="${fg}" stroke-width="2"/>
        </svg>`;
      return "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svg);
    };

    const replaceImg = (img) => {
      const src = img.getAttribute("src") || "";
      if (!src || !isDemoUrl(src)) return;

      const wAttr = parseInt(img.getAttribute("width") || "", 10);
      const hAttr = parseInt(img.getAttribute("height") || "", 10);

      // Prefer explicit width/height; fallback to layout size; then sane defaults.
      const w =
        Number.isFinite(wAttr) && wAttr > 0 ? wAttr : img.clientWidth || 300;
      const h =
        Number.isFinite(hAttr) && hAttr > 0 ? hAttr : img.clientHeight || 200;

      img.setAttribute("src", svgDataUri(w, h));
    };

    // Initial pass
    document.querySelectorAll("img").forEach(replaceImg);

    // If images are injected later (client-side rendering), keep it stable.
    const obs = new MutationObserver(() => {
      document.querySelectorAll("img").forEach(replaceImg);
    });
    obs.observe(document.documentElement, { childList: true, subtree: true });

    // Expose for debugging if needed
    window.__vrtPicsumReplacerInstalled = true;
  });
};
