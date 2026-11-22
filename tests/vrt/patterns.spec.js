// @ts-check
const fs = require("fs");
const path = require("path");
const { test, expect } = require("@playwright/test");
const interceptImages = require("./support/interceptImages");

// Every folder in templates/patterns is one UI pattern, named by its ID.
// A new pattern is covered automatically; a removed one disappears.
const patternsDir = path.resolve(__dirname, "../../templates/patterns");
const patterns = fs
  .readdirSync(patternsDir, { withFileTypes: true })
  .filter((entry) => entry.isDirectory())
  .map((entry) => entry.name)
  .sort();

const VARIANT_CLASS = /pattern-preview__markup--variant_(\S+)/;

for (const pattern of patterns) {
  test(pattern, async ({ page }) => {
    await interceptImages(page);
    await page.goto(`admin/appearance/ui/patterns/${pattern}`, { waitUntil: "networkidle" });
    const previews = page.locator(".pattern-preview__markup");
    await expect(previews.first()).toBeAttached();

    // The pattern page renders every variant in its own wrapper; take one
    // screenshot per variant.
    const count = await previews.count();
    for (let i = 0; i < count; i++) {
      const preview = previews.nth(i);
      const variant = (await preview.getAttribute("class"))?.match(VARIANT_CLASS)?.[1] ?? "default";
      await expect.soft(preview).toHaveScreenshot(`${pattern}--${variant}.png`);
    }
  });
}
