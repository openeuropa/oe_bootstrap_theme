const fs = require("fs");
const path = require("path");
const cheerio = require("cheerio");
const { chromium } = require("playwright");

const BASE = process.env.VRT_BASE_URL || "http://localhost:8080/build";
const PATTERNS_INDEX_PATH = process.env.PATTERNS_INDEX_PATH || "/admin/appearance/ui/patterns";
const OUT_FILE = path.join(__dirname, "scenarios", "patterns.json");

function absolutize(href) {
  if (!href) return null;
  if (href.startsWith("http")) return href;
  if (href.startsWith("/")) return BASE + href;
  return BASE + "/" + href;
}

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  await require(path.join(__dirname, "engine_scripts", "playwright", "drupalLogin.js"))(page, {
    baseUrl: BASE,
  });

  await page.goto(BASE + PATTERNS_INDEX_PATH, { waitUntil: "networkidle" });
  const html = await page.content();
  const $ = cheerio.load(html);

  // Common approach: grab all internal links and then filter by URL pattern.
  const links = [];
  $("a[href]").each((_, a) => {
    const href = $(a).attr("href");
    const text = $(a).text().trim().replace(/\s+/g, " ");
    links.push({ href, text });
  });

  // Keep only component preview pages.
  const previewLinks = links
    .map(l => ({ ...l, url: absolutize(l.href) }))
    .filter(l => l.url && l.url.startsWith(BASE + PATTERNS_INDEX_PATH))
    .filter((l, idx, arr) => arr.findIndex(x => x.url === l.url) === idx);

  if (previewLinks.length === 0) {
    console.warn("No preview links found.");
  }

  // Build scenarios.
  const scenarios = previewLinks.map((l) => ({
    label: l.text ? `Pattern - ${l.text}` : `Pattern - ${l.url.replace(BASE, "")}`,
    url: l.url,
    selectors: [".pattern-preview__markup"],
    delay: 300,
  }));

  await browser.close();

  fs.writeFileSync(OUT_FILE, JSON.stringify(scenarios, null, 2));
  console.log(`Generated ${scenarios.length} scenarios -> ${OUT_FILE}`);
})();
