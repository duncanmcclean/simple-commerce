# Installation Guide

## Pre-install checks

1. Check that you have the latest version of the Statamic 3 beta installed. You can update by using `composer update statamic/cms`

## Testing steps

During testing steps, Commerce for Statamic won't be installable via Composer but instead it requires quite a bit of manual installation.

1. clone this repository to `./addons/damcclean/commerce`
2. install composer dependencies inside `commerce` directory `composer install`
3. add repository to main `composer.json`

```json
  "repositories": [
        {
            "type": "path",
            "url": "addons/damcclean/commerce"
        }
    ]
```

4. Add `damcclean/commerce` as a dependency in your `composer.json` file.

```json
    "require": {
      "damcclean/commerce": "dev-master",
    }
```

5. Run the install command and follow the setup guide

```shell script
php please commerce:install
```
