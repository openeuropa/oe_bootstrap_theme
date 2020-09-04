const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const globImporter = require('node-sass-glob-importer');
// const BrowserSyncPlugin = require('browser-sync-webpack-plugin')

var config = {
  entry: "./src/assets/js/index.js",
  output: {
    path: path.resolve(__dirname, "assets/"),
    filename: "js/scripts.js",
    publicPath: ""
  },
  optimization: {
    minimizer: [
      new TerserPlugin(),
      new OptimizeCSSAssetsPlugin()
    ],
  },
  module: {
    rules: [
      //babel
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: "babel-loader",
        options: {
          presets: ["@babel/preset-env"]
        }
      },
      //style and css extract
      {
        test: [/.css$|.scss$/],
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
          "postcss-loader",
          {
            loader: "sass-loader",
            options: {
              importer: globImporter()
            }
          }
        ]
      }
    ]
  },
  resolve: {
    alias: {
      '@scss': path.resolve(__dirname, 'src/assets/scss'),
      '@': path.resolve(__dirname, 'src')
    },
    modules: [
      'node_modules',
      path.resolve(__dirname, 'src')
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "css/styles.css"
    }),
    /*
    new BrowserSyncPlugin({
      host: 'localhost',
      port: 3000,
      server: { baseDir: ['dist'] }
    })
    */
  ]
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
  }
  return config;
};
