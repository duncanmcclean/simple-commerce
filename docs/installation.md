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

5. Run the install command, it'll copy over things like blueprints and config files.

```shell script
php artisan commerce:install
```

6. Another thing you'll want to do before things start working is to add some URLs into your app's `VerifyCsrfToken.php` file. There's a statamic bug meaning we can't use csrf tokens in some places. We'll hopefully have this fixed before we launch.

```php
<?php

...

protected $except = [
        '/cart', '/cart/*', '/checkout', '/products/search',
];
```

7. Last but not least, you'll want to [setup Stripe](./stripe.md#setting-stripe-up) or you won't be able to accept payments.
