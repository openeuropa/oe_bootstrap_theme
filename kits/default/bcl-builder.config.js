const path = require("path");
const fs = require("fs");

/* Specify base_theme relative path */
const baseThemeCandidates = [
  // From lib folder (DEV build).
  "../../",
  // From dist/web/themes/custom folder (DIST build).
  "../../contrib/oe_bootstrap_theme"
];
const baseThemeFolder = baseThemeCandidates
  .map(function (value) {
    return path.resolve(__dirname, value);
  })
  .find(function (value) {
    return fs.existsSync(value);
  });

const outputFolder = path.resolve(__dirname);
const nodeModules = path.resolve(__dirname, "./node_modules");

// SCSS includePaths
const includePaths = [nodeModules];

module.exports = {
  styles: [
    {
      entry: path.resolve(baseThemeFolder, "resources/sass/oe_bootstrap_theme.style.scss"),
      dest: path.resolve(outputFolder, "assets/css/oe_bootstrap_theme.style.min.css"),
      options: {
        includePaths,
        minify: true,
        sourceMap: "file",
      },
    },
  ],
  styles: [
    {
      entry: path.resolve(outputFolder, "resources/sass/default.style.scss"),
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
      from: [
        path.resolve(
          nodeModules,
          "@openeuropa/bcl-theme-default/css/**"
        ),
      ],
      to: path.resolve(outputFolder, "assets/css"),
      options: { up: true },
    },
    {
      from: [
        path.resolve(
          nodeModules,
          "@openeuropa/bcl-theme-default/js/**"
        ),
      ],
      to: path.resolve(outputFolder, "assets/js"),
      options: { up: true },
    },
    {
      from: [
        path.resolve(
          nodeModules,
          "bootstrap-ie11/css/**"
        ),
      ],
      to: path.resolve(outputFolder, "assets/css"),
      options: { up: true },
    },
    {
      from: [
        path.resolve(
          baseThemeFolder,
          "resources/js/libraries/extend/progress.js"
        ),
      ],
      to: path.resolve(outputFolder, "assets/js"),
      options: { up: true },
    },
    {
      from: [
        path.resolve(
          nodeModules,
          "@openeuropa/bcl-theme-default/icons/bootstrap-icons.svg"
        ),
      ],
      to: path.resolve(outputFolder, "assets/icons"),
      options: { up: true },
    },
  ],
};
