/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your application. See https://github.com/JeffreyWay/laravel-mix.
 |
 */
const path = require("path");
const fs = require("fs");
const mix = require('laravel-mix');
const MixGlob = require('laravel-mix-glob');

/* Specify base_theme relative path */
const baseThemeCandidates = [
  // From lib folder (DEV build).
  "../../../web/themes/contrib/eua_theme",
  // From dist/web/themes/custom folder (DIST build).
  "../../contrib/eua_theme"
];
const baseThemePath = baseThemeCandidates
  .map(function (value) {
    return path.resolve(__dirname, value);
  })
  .find(function (value) {
    return fs.existsSync(value);
  });

/*
 |--------------------------------------------------------------------------
 | Configuration
 |--------------------------------------------------------------------------
 */
mix.webpackConfig({
  resolve: {
    alias: {
      "eua_scss": `${baseThemePath}/resources/sass`,
      "@eua_js": `${baseThemePath}/resources/js`,
    },
  },
});

if (!mix.inProduction()) {
  // Enable source maps.
  // See https://www.drupal.org/project/radix/issues/3021020#comment-13116504
  mix
    .webpackConfig({
      devtool: 'source-map'
    })
    .sourceMaps();
}

/*
 |--------------------------------------------------------------------------
 | Configuration
 |--------------------------------------------------------------------------
 */
mix
  .setPublicPath('assets')
  .disableNotifications()
  .options({
    processCssUrls: false,
    postCss: [require("autoprefixer")],
  })
  .webpackConfig({
    module: {
      rules: [
        {
          test: /\.scss$/,
          loader: 'import-glob-loader',
        }
      ]
    }
  });

/*
 |--------------------------------------------------------------------------
 | Browsersync
 |--------------------------------------------------------------------------
 */

// Add MIX_PROXY setting in your .env file,
// duplicate .env.dist to .env and change setting according to your environment.
const proxy = process.env.MIX_PROXY;

mix.browserSync({
  proxy: proxy,
  files: [
    'assets/js/**/*.js',
    'assets/css/**/*.css',
  ],
  stream: true,
});

/*
 |--------------------------------------------------------------------------
 | Globs: https://www.npmjs.com/package/laravel-mix-glob
 |--------------------------------------------------------------------------
 */

const mixGlob = new MixGlob({mix});

/*
 |--------------------------------------------------------------------------
 | SASS
 |--------------------------------------------------------------------------
 */

// Every file named *.compile.scss within resources/sass/ folder,
// will be compiled to assets/css folder keeping same folder strucure from base parameter.
mixGlob.sass(['resources/sass/**/*.compile.scss'], 'css', null, {
  base: 'resources/sass/'
});

// Bootstrap Ie11 support scss files:
// https://coliff.github.io/bootstrap-ie11/
mix.sass('./node_modules/bootstrap-ie11/scss/bootstrap-ie11.scss', 'css');

/*
 |--------------------------------------------------------------------------
 | JS
 |--------------------------------------------------------------------------
 */

// Load Bootstrap globally
mix.autoload({
  'bootstrap': ['bootstrap'],
});

// Every file named *.compile.js within resources/js/ folder,
// will be compiled to assets/js folder keeping same folder strucure from base parameter.
mixGlob.js(['resources/js/**/*.compile.js'], 'js', null, {
  base: 'resources/js'
});
