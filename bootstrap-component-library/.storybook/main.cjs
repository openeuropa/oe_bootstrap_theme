const path = require("path");
const { dirname, join } = path;

const getAbsolutePath = (value) => {
  return dirname(require.resolve(join(value, "package.json")));
};

const resolvePath = (relativePath) => path.resolve(__dirname, relativePath);

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
      environmentModulePath: resolvePath("environment.js"),
    },
  });

  // Remove ProgressPlugin
  config.plugins = config.plugins.filter(
    (plugin) => plugin.constructor.name !== "ProgressPlugin"
  );

  // Add fallbacks for browser compatibility
  config.resolve = {
    ...config.resolve,
    alias: {
      ...config.resolve?.alias,

      // Components
      "@openeuropa/bcl-accordion": resolvePath("../src/components/bcl-accordion"),
      "@openeuropa/bcl-heading": resolvePath("../src/components/bcl-heading"),

      // Compositions
      "@openeuropa/bcl-base-templates": resolvePath("../src/compositions/bcl-base-templates"),
      "@openeuropa/bcl-contact-form": resolvePath("../src/compositions/bcl-contact-form"),
      "@openeuropa/bcl-multilingual": resolvePath("../src/compositions/bcl-multilingual"),
      "@openeuropa/bcl-project": resolvePath("../src/compositions/bcl-project"),
      "@openeuropa/bcl-file": resolvePath("../src/compositions/bcl-file"),
      "@openeuropa/bcl-featured-media": resolvePath("../src/compositions/bcl-featured-media"),
      "@openeuropa/bcl-banner": resolvePath("../src/compositions/bcl-banner"),
      "@openeuropa/bcl-language-list": resolvePath("../src/compositions/bcl-language-list"),
      "@openeuropa/bcl-links-block": resolvePath("../src/compositions/bcl-links-block"),
      "@openeuropa/bcl-inpage-navigation": resolvePath("../src/compositions/bcl-inpage-navigation"),
      "@openeuropa/bcl-content-banner": resolvePath("../src/compositions/bcl-content-banner"),
      "@openeuropa/bcl-user": resolvePath("../src/compositions/bcl-user"),
      "@openeuropa/bcl-project-status": resolvePath("../src/compositions/bcl-project-status"),
      "@openeuropa/bcl-timeline": resolvePath("../src/compositions/bcl-timeline"),
      "@openeuropa/bcl-search-form": resolvePath("../src/compositions/bcl-search-form"),
      "@openeuropa/bcl-listing": resolvePath("../src/compositions/bcl-listing"),
      "@openeuropa/bcl-recent-activities": resolvePath("../src/compositions/bcl-recent-activities"),
      "@openeuropa/bcl-color-scheme": resolvePath("../src/compositions/bcl-color-scheme"),

      // Data
      "@openeuropa/bcl-data-accordion": resolvePath("../src/data/accordion"),
      "@openeuropa/bcl-data-blockquote": resolvePath("../src/data/blockquote"),
      "@openeuropa/bcl-data-link": resolvePath("../src/data/link"),

      // Add any others you need here
    },
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
