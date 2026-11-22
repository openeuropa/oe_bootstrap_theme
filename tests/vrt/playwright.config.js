// @ts-check
const { defineConfig } = require("@playwright/test");

const baseURL = `${(process.env.VRT_BASE_URL || "http://localhost:8080/build").replace(/\/$/, "")}/`;
const authFile = `${__dirname}/.auth/admin.json`;

// Same viewports as the previous BackstopJS setup.
const viewports = {
  mobile: { width: 375, height: 667 },
  tablet: { width: 768, height: 1024 },
  desktop: { width: 1280, height: 800 },
  wide: { width: 1440, height: 900 },
};

module.exports = defineConfig({
  testDir: __dirname,
  outputDir: `${__dirname}/test-results`,
  // Reference screenshots, one folder per viewport. No platform suffix: the
  // references are always generated and compared in the web container.
  snapshotPathTemplate: "{testDir}/screenshots/{projectName}/{arg}{ext}",
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  reporter: [
    ["list"],
    ["html", { outputFolder: `${__dirname}/html_report`, open: "never" }],
  ],
  use: {
    baseURL,
    trace: "retain-on-failure",
  },
  expect: {
    toHaveScreenshot: {
      animations: "disabled",
      caret: "hide",
      stylePath: `${__dirname}/support/screenshot.css`,
      // Same tolerance as the BackstopJS default (0.1%).
      maxDiffPixelRatio: 0.001,
    },
  },
  projects: [
    {
      name: "setup",
      testMatch: /auth\.setup\.js/,
    },
    ...Object.entries(viewports).map(([name, viewport]) => ({
      name,
      testMatch: /\.spec\.js/,
      dependencies: ["setup"],
      use: { viewport, storageState: authFile },
    })),
  ],
});
