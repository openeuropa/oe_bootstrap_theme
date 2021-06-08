# Sub-theme starter kits

This directory contains starter kits to create sub-themes.

This is inspired by the [Radix](https://www.drupal.org/project/radix) base theme, where these starter kits are placed under `/src/kits/` instead of just `/kits/`. For the OpenEuropa Bootstrap theme it was decided to reserve `/src/` for PSR-4 class files only.

The `*.info.yml` file of each starterkit is renamed, to hide it from Drupal's extension discovery.

## Prerequesites
You need a tool (command-line, IDE or text editor) that can easily replace strings in multiple files at once.

Further requirements are found in the README.md of the respective starterkit.

## Usage
To create a sub-theme from the `/kits/default/` starterkit:

1. Choose a name and machine name for your new sub-theme, e.g. "Beach time" with machine name `beachtime`.
1. Copy the `/kits/default/` into the place where you want to create the sub-theme.
1. Rename the `default.info.hidden.yml` -> `beachtime.info.yml`.
1. Rename files that are currently named `default.*` to `beachtime.*`.
    E.g. `default.theme` becomes `beachtime.theme`.
1. Find + replace strings in all files:
    - "OE_BOOTSTRAP_THEME_SUBTHEME_MACHINE_NAME" -> "beachtime".
    - "OE_BOOTSTRAP_THEME_SUBTHEME_NAME" -> "Beach time".
    - "OE_BOOTSTRAP_THEME_SUBTHEME_DESCRIPTION" -> Choose a description.
1. Review the changes, especially for the human name and description.
  This is also a good time for a git commit!
1. Check the README.md in your new sub-theme for additional steps.
