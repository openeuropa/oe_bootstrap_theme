// @ts-check
const { test: setup } = require("@playwright/test");
const drupalLogin = require("./support/drupalLogin");

// Log in once and share the session with all screenshot tests.
setup("log in to Drupal", async ({ page }) => {
  await drupalLogin(page);
  await page.context().storageState({ path: `${__dirname}/.auth/admin.json` });
});
