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
const mix = require('laravel-mix');

/* Specify base_theme relative path */
const baseTheme = "../eua_theme/";
const baseThemePath = path.resolve(__dirname, baseTheme);

/* Live reloads URL pushing */
const proxy = 'http://drupal.local';

/*
 |--------------------------------------------------------------------------
 | Configuration
 |--------------------------------------------------------------------------
 */
mix.webpackConfig({
  resolve: {
    alias: {
      "eua_scss": `${baseThemePath}/src/sass`,
      "@eua_js": `${baseThemePath}/src/js`,
    },
  },
});

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
  });

/*
 |--------------------------------------------------------------------------
 | Browsersync
 |--------------------------------------------------------------------------
 */
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
 | SASS
 |--------------------------------------------------------------------------
 */
mix.sass('src/sass/EUA_SUBTHEME_MACHINE_NAME.style.scss', 'css');

/*
 |--------------------------------------------------------------------------
 | JS
 |--------------------------------------------------------------------------
 */
mix.js('src/js/EUA_SUBTHEME_MACHINE_NAME.script.js', 'js');
