# OpenEuropa Bootstrap base theme

Drupal 8/9 theme based on [Bootstrap 5](https://v5.getbootstrap.com/), [UI Patterns](https://github.com/nuvoleweb/ui_patterns/) and the [OpenEuropa Bootstrap Component Library](https://github.com/openeuropa/bootstrap-component-library).

## Usage as a dependency

### Requirements

The package is meant for Drupal projects that [manage their Drupal dependencies via Composer](https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies#managing-contributed).

The usage of [Docker](https://www.docker.com/get-docker) in the dependent project is suggested, but not required.

Check the [composer.json](composer.json) for required PHP version and other dependencies.

### Add the composer package

Add this manually in composer.json, or combine with existing entries:

```
    "extra": {
        "artifacts": {
            "openeuropa/oe_bootstrap_theme": {
                "dist": {
                    "url": "https://github.com/{name}/releases/download/{pretty-version}/{project-name}-{pretty-version}.zip",
                    "type": "zip"
                }
            }
        }
    }
```

Require with composer:

```bash
composer require openeuropa/oe_bootstrap_theme:^1.0@alpha
```

### Review the installation

Review the installed version with `composer info | grep oe_`.

Review the installation directory with `composer info openeuropa/oe_bootstrap_theme | grep path`. Depending on your setup, this could be `build/themes/contrib/oe_bootstrap_theme`.

If installation was successful, the instance of `oe_bootstrap_theme` should contain a number of subdirectories within `/assets/`, including `/assets/bcl/`.

### Enable and configure

Enable the required helper module:

```bash
./vendor/bin/drush en oe_bootstrap_theme_helper
```

Enable the theme itself and set it as default:

```bash
./vendor/bin/drush config-set system.theme default oe_bootstrap_theme
```

### Generate a sub-theme

The package provides a [task-runner](https://github.com/openeuropa/task-runner) command to generate a sub-theme.

```bash
# Install the task runner:
composer require openeuropa/task-runner
# Learn more about the create-subtheme command:
./vendor/bin/run help oe_bootstrap_theme:create-subtheme
# Generate a sub-theme
./vendor/bin/run oe_bootstrap_theme:create-subtheme [...]
```

After using the command, first commit the generated sub-theme in git, then review _all of it_, and determine which parts you can remove or you have to alter.

An older, manual way to create a sub-theme is described in [kits/README.md](kits/README.md).

## Development setup

### Using LAMP stack or similar

This is not officially supported. You are on your own.

### Using Docker Compose

Alternatively, you can build a development site using [Docker](https://www.docker.com/get-docker) and
[Docker Compose](https://docs.docker.com/compose/) with the provided configuration.

Docker provides the necessary services and tools such as a web server and a database server to get the site running,
regardless of your local host configuration.

#### Requirements

- [Docker](https://www.docker.com/get-docker)
- [Docker Compose](https://docs.docker.com/compose/)

#### Override docker settings

The package provides default settings for Docker Compose in `docker-compose.yml`. Most of the time these are sufficient.

An optional `docker-compose.override.yml` file can be created to selectively override specific values, or to define entirely new services.

For services that are defined in both files, Docker Compose applies merge rules that are documented in [the official Docker Compose documentation](https://docs.docker.com/compose/extends/).

#### Start the container

If you have other (daemonized) containers running, you might want to stop them first:

```bash
docker stop $(docker ps -q)
```

To start, run:

```bash
docker-compose up
```

It's advised to not daemonize `docker-compose` so you can turn it off (`CTRL+C`) quickly when you're done working.
However, if you'd like to daemonize it, you have to add the flag `-d`:

```bash
docker-compose up -d
```

#### Optionally purge existing installation

If you already had the package installed, and want a clean start:

```bash
docker-compose exec web rm composer.lock
docker-compose exec web rm -rf vendor/
docker-compose exec web rm -rf build/
```

#### Install and build

Install dependencies, build artifacts, and install Drupal.

```bash
docker-compose exec -u node node npm install
docker-compose exec -u node node npm run build
docker-compose exec web composer install
docker-compose exec web ./vendor/bin/run drupal:site-install
```

#### Visit the development site

Using default configuration, the development site files should be available in the `build` directory and the development site should be available at: [http://127.0.0.1:8080/build](http://127.0.0.1:8080/build) or [http://web:8080/build](http://web:8080/build).

#### Run the tests

Run the grumphp checks:

```bash
docker-compose exec web ./vendor/bin/grumphp run
```

Run the phpunit tests:

```bash
docker-compose exec web ./vendor/bin/phpunit
```

## Patch BCL components

BCL components can be patched by using the [`patch-package`](https://www.npmjs.com/package/patch-package) NPM project.

To patch a component:

1. Modify its source files directly in `./node_modules/@openeuropa/bcl-theme-default`
2. Run:

```bash
docker-compose exec -u node node git config --global user.email "name@example.com"
docker-compose exec -u node node git config --global user.name "Name"
docker-compose exec -u node node npx patch-package @openeuropa/bcl-theme-default --patch-dir=patches/npm
```

Patches will be generated in `./patches/npm` and applied when running `npm install`.\
**Note:** generate patches **only** inside the docker container to use the same version of npm/npx.

## Contribute

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/oe_bootstrap_theme/tags).

## Upgrade from older versions

### Upgrade to 1.0.0-alpha8

#### Paragraphs migration

Paragraphs-related theming and functionality has been moved from the [OpenEuropa Bootstrap base theme](https://github.com/openeuropa/oe_bootstrap_theme) to [OpenEuropa Whitelabel](https://github.com/openeuropa/oe_whitelabel).

If you are using `openeuropa/oe_whitelabel`, and you want paragraphs functionality, you should upgrade it to `1.0.0-alpha6` or higher, and refer to the upgrade instructions found there.
