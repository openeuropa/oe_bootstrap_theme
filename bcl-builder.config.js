const path = require("path");

const outputFolder = path.resolve(__dirname);
const nodeModules = path.resolve(__dirname, "./node_modules");

// SCSS includePaths
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
      from: ["node_modules/@openeuropa/bcl-theme-default/css/**"],
      to: path.resolve(outputFolder, "assets/css"),
      options: { up: true },
    },
    {
      from: ["node_modules/@openeuropa/bcl-theme-default/js/**"],
      to: path.resolve(outputFolder, "assets/js"),
      options: { up: true },
    },
    {
      from: ["node_modules/bootstrap-ie11/css/**"],
      to: path.resolve(outputFolder, "assets/css"),
      options: { up: true },
    },
    {
      from: [path.resolve(outputFolder, "resources/js/libraries/extend/progress.js")],
      to: path.resolve(outputFolder, "assets/js"),
      options: { up: true },
    },
    {
      from: [path.resolve(outputFolder, "resources/js/libraries/extend/form_select_multiple.js")],
      to: path.resolve(outputFolder, "assets/js"),
      options: { up: true },
    },
    {
      from: ["node_modules/@openeuropa/bcl-theme-default/icons/bootstrap-icons.svg"],
      to: path.resolve(outputFolder, "assets/icons"),
      options: { up: true },
    },
    {
      from: ["node_modules/@openeuropa/bcl-theme-default/templates/**/*.twig"],
      to: path.resolve(outputFolder, "assets/bcl"),
      options: { up: 4 },
    },
  ],
};
