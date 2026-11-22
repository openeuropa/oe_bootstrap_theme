// Replace demo images (picsum) with deterministic SVG placeholders.
//
// Pattern previews use random picsum images, both in <img> tags and as CSS
// background images. Answering the network requests covers both cases and
// works before the page renders, so screenshots never show a random image.

const DEMO_HOSTS = /^https?:\/\/(fastly\.)?picsum\.(photos|dev)\//;

const svg = (w, h) => {
  const bg = "#6b3fa0"; // purple
  const fg = "#2ecc71"; // green
  // Crisp edges helps reduce antialiasing diffs.
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}" shape-rendering="crispEdges">
    <rect width="${w}" height="${h}" fill="${bg}"/>
    <rect x="1" y="1" width="${Math.max(0, w - 2)}" height="${Math.max(0, h - 2)}" fill="none" stroke="${fg}" stroke-width="2"/>
    <line x1="0" y1="0" x2="${w}" y2="${h}" stroke="${fg}" stroke-width="2"/>
    <line x1="${w}" y1="0" x2="0" y2="${h}" stroke="${fg}" stroke-width="2"/>
  </svg>`;
};

// picsum URLs end with the size: /1000/500, /seed/x/1000/500 or /300 (square).
const sizeFromUrl = (url) => {
  const numbers = new URL(url).pathname.split("/").filter((part) => /^\d+$/.test(part)).map(Number);
  const [w, h] = numbers.slice(-2);
  if (numbers.length >= 2) return [w, h];
  if (numbers.length === 1) return [numbers[0], numbers[0]];
  return [300, 200];
};

module.exports = async (page) => {
  await page.route(DEMO_HOSTS, (route) => {
    const [w, h] = sizeFromUrl(route.request().url());
    return route.fulfill({ contentType: "image/svg+xml", body: svg(w, h) });
  });
};
