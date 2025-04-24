import { dirname, join } from "path";
const path = require("path");

let stories = ["../bcl-stories/!(test*|deprecated*).story.js"];

const webpackFinal = (config) => {
  config.module.rules.push({
    test: /\.twig$/,
    loader: "twing-loader",
    options: {
      environmentModulePath: path.resolve(`${__dirname}/environment.js`),
    },
  });

  config.plugins.forEach((plugin, i) => {
    if (plugin.constructor.name === "ProgressPlugin") {
      config.plugins.splice(i, 1);
    }
  });

  return config;
};

const config = {
  framework: {
    name: getAbsolutePath("@storybook/html-webpack5"),
    options: {},
  },

  staticDirs: ["../../../../../assets/"],
  stories,
  webpackFinal,

  features: {
    postcss: false,
  },

  docs: {},
};

export default config;

function getAbsolutePath(value) {
  return dirname(require.resolve(join(value, "package.json")));
}
