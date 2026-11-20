module.exports = async (browserContext, scenario) => {
  // Example: Load cookies from a scenario or predefined set
  const cookies = scenario.cookies || []; // Adjust as needed

  for (let cookie of cookies) {
    await browserContext.addCookies([cookie]);
  }
};
