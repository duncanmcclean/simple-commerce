# Installation Guide

## Pre-install checks

1. Check that you have the latest version of the Statamic 3 beta installed. You can update by using `composer update statamic/cms`

## Testing steps

During testing, Commerce for Statamic won't be installable via Composer but instead it requires quite a bit of manual installation.

1. Clone this repository to `./addons/damcclean/commerce` - `git clone git@github.com:damcclean/commerce addons/damcclean/commerce`
2. Run `composer install` inside the `./addons/damcclean/commerce` folder.
3. In your site's main `composer.json` file, add the following few lines:

```json
  "require": {
      ...
      "damcclean/commerce": "dev-master"
  }
  
  ...

  "repositories": [
        {
            "type": "path",
            "url": "addons/damcclean/commerce"
        }
    ]
```

4. Run `composer install`

5. Run the install command, it'll guide you through the rest of the process.

```shell script
php artisan commerce:install
```
