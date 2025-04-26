const path = require("path");

const outputFolder = path.resolve(__dirname);
const nodeModules = path.resolve(__dirname, "./node_modules");
const bclThemeDefault = path.resolve(__dirname, "bootstrap-component-library/src/themes/default");

const includePaths = [nodeModules];

module.exports = {
  styles: [
    {
      entry: path.resolve(outputFolder, "resources/sass/oe_bootstrap_theme.style.scss"),
      dest: path.resolve(outputFolder, "assets/css/oe_bootstrap_theme.style.min.css"),
      options: {
        includePaths,
        minify: true,
        sourceMap: "file",
      },
    },
  ],
  copy: [
    {
      from: [path.join(bclThemeDefault, "css/**")],
      to: path.resolve(outputFolder, "assets/css"),
      options: { up: true },
    },
    {
      from: [path.join(bclThemeDefault, "js/**")],
      to: path.resolve(outputFolder, "assets"),
      options: { up: 9 },
    },
    {
      from: ["node_modules/bootstrap-ie11/css/**"],
      to: path.resolve(outputFolder, "assets/css"),
      options: { up: true },
    },
    {
      from: [path.join(bclThemeDefault, "icons/bcl-default-icons.svg")],
      to: path.resolve(outputFolder, "assets/icons"),
      options: { up: true },
    },
    {
      from: [path.join(bclThemeDefault, "templates/**/*.twig")],
      to: path.resolve(outputFolder, "assets/bcl"),
      options: { up: 10 },
    },
    {
      from: [path.join(bclThemeDefault, "logos/ec/positive/*.svg")],
      to: path.resolve(outputFolder, "assets/logos/ec"),
      options: { up: true },
    },
    {
      from: [path.join(bclThemeDefault, "logos/eu/standard-version/**")],
      to: path.resolve(outputFolder, "assets/logos/eu"),
      options: { up: true },
    },
    {
      from: [path.join(bclThemeDefault, "logos/eu/condensed-version/**")],
      to: path.resolve(outputFolder, "assets/logos/eu/mobile"),
      options: { up: true },
    },
  ],
  rename: [
    {
      from: path.resolve(outputFolder, "assets/bcl/**/*"),
      to: "bcl-",
      options: {
        search: "!(bcl-)*.html.twig",
        operation: "prefix",
      },
    },
  ],
};
