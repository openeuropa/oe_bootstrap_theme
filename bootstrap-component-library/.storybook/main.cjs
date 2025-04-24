const { dirname, join } = require("path");

const getAbsolutePath = (value) => {
  return dirname(require.resolve(join(value, "package.json")));
};

const stories = ["../src/*/*/*.story.js"];

const addons = [
  getAbsolutePath("@storybook/addon-controls"),
];

const webpackFinal = async (config) => {
  // Add .twig loader
  config.module.rules.push({
    test: /\.twig$/,
    loader: "twing-loader",
    options: {
      environmentModulePath: require("path").resolve(`${__dirname}/environment.js`),
    },
  });

  config.module.rules.push({
    test: /\.s[ac]ss$/i,
    use: [
      "style-loader",
      "css-loader",
      {
        loader: "sass-loader",
        options: {
          implementation: require("sass"),
        },
      },
    ],
  });

  // Remove ProgressPlugin to prevent potential issues
  config.plugins = config.plugins.filter(
    (plugin) => plugin.constructor.name !== "ProgressPlugin"
  );

  // Add fallbacks for browser compatibility with some Node.js built-ins
  config.resolve = {
    ...config.resolve,
    fallback: {
      ...config.resolve?.fallback,
      fs: false,
      path: require.resolve("path-browserify"),
    },
  };

  // Fix for pnpm + webpack snapshot issues
  config.snapshot = {
    ...config.snapshot,
    managedPaths: [/(^|[/\\])node_modules([/\\]|$)/],
  };

  return config;
};

module.exports = {
  framework: {
    name: getAbsolutePath("@storybook/html-webpack5"),
    options: {},
  },
  staticDirs: ['../assets'],
  stories,
  addons,
  webpackFinal,
  features: {
    postcss: false,
  },
  docs: {},
};
