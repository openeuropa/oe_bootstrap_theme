# Installation

EUA_SUBTHEME_NAME theme uses [Webpack](https://webpack.js.org) to compile and bundle SASS and JS.

#### Step 1
Make sure you have Node and npm installed.
You can read a guide on how to install node here: https://docs.npmjs.com/getting-started/installing-node

If you prefer to use [Yarn](https://yarnpkg.com) instead of npm, install Yarn by following the guide [here](https://yarnpkg.com/docs/install).

#### Step 2
Go to the root of EUA_SUBTHEME_NAME theme and run the following commands: `npm install` or `yarn install`.

#### Step 3
Update `baseTheme` variable in **webpack.mix.json** file to inherit some sass and javascript files needed from the base theme. A relative path to the base theme must be specified.

#### Step 4
Update `proxy` variable in **webpack.mix.json** file for live CSS Reload & Browser Syncing during watch task.

#### Step 5
Run the following command to compile Sass and watch for changes: `npm run watch` or `yarn watch`.
