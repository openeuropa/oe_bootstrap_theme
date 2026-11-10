const LOGIN_PATH = "/user/login";
const SUBMIT_SELECTOR = 'input#edit-submit, button#edit-submit, input[type="submit"]';

const resolveBaseUrl = (scenario = {}) =>
  (process.env.VRT_BASE_URL || scenario.baseUrl || "http://localhost:8080/build").replace(/\/$/, "");

const resolveUser = (scenario = {}) =>
  process.env.DRUPAL_USER || scenario.drupalUser || "admin";

const resolvePass = (scenario = {}) =>
  process.env.DRUPAL_PASS || scenario.drupalPass || "admin";

module.exports = async (page, scenario = {}) => {
  const baseUrl = resolveBaseUrl(scenario);
  const user = resolveUser(scenario);
  const pass = resolvePass(scenario);
  const targetUrl = `${baseUrl}${LOGIN_PATH}`;

  await page.goto(targetUrl, { waitUntil: "networkidle" });

  await page.waitForSelector('input[name="name"]', {
    state: "visible",
    timeout: 15000,
  });

  await page.fill('input[name="name"]', user);
  await page.fill('input[name="pass"]', pass);

  await Promise.all([
    page.waitForNavigation({ waitUntil: "networkidle" }),
    page.click(SUBMIT_SELECTOR),
  ]);

  await page.waitForSelector(".toolbar", {
    state: "attached",
    timeout: 10000,
  });
};
