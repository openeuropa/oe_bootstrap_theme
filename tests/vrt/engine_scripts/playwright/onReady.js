module.exports = async (page, scenario, viewport, isReference, browserContext) => {
  console.log('SCENARIO > ' + scenario.label + ' (' + viewport.label + ')');
  await require('./overrideCSS')(page, scenario);
  await require('./clickAndHoverHelper')(page, scenario);
  await require('./interceptImages')(page, scenario);
};
