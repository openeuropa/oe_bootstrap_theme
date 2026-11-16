# Installation

OE_BOOTSTRAP_THEME_SUBTHEME_NAME theme uses [Webpack](https://webpack.js.org) to compile and bundle SASS and JS.

#### Step 1
Make sure you have Node.js 22 or 24 and pnpm 10.34.9 installed. With Corepack
installed, run `corepack enable` to use the pnpm version pinned in `package.json`.

#### Step 2
Go to the root of OE_BOOTSTRAP_THEME_SUBTHEME_NAME theme and install the locked dependencies:

```sh
pnpm install --frozen-lockfile --ignore-scripts
```

#### Step 3
Build the assets explicitly because install-time lifecycle scripts are disabled:

```sh
pnpm run build
```

Then watch for Sass changes during development:

```sh
pnpm run watch
```

*Important:* `style` and `copy` tasks are defined in the bcl-builder config file. You can change or improve them based on your needs. [bcl-builder.config.js](bcl-builder.config.js)

## Overriding inherited templates
Add template file with the same name in your sub-theme folder to have it override the template from the parent theme.
[layout](layout), [overrides](overrides), [paragraphs](paragraphs), [patterns](patterns) folders are there for this purpose.
