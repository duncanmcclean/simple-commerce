# Installation Guide

## Prerequisites

Before installing Commerce you'll want to check you're running the latest version of Statamic 3 and you'll also want to have [your database](https://laravel.com/docs/5.8/database) setup.

## Testing steps

During testing, Commerce for Statamic won't be installable via Composer but instead it requires quite a bit of manual installation.

1. Clone this repository to `./addons/damcclean/commerce` - `git clone git@github.com:damcclean/commerce.git addons/damcclean/commerce`
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

4. Run `composer install && composer update`

5. Run `php artisan vendor:publish` and select the option `Damcclean\Commerce\CommerceServiceProvider`.

6. You'll also need to run our migrations which will create the required database tables for Commerce `php artisan migrate`

7. Another thing you'll want to do before things start working is to add some URLs into your app's `VerifyCsrfToken.php` file. There's a statamic bug meaning we can't use csrf tokens in some places. We'll hopefully have this fixed before we launch.

```php
<?php

...

protected $except = [
        '/cart', '/cart/*', '/checkout', '/products/search',
];
```

8. Last but not least, you'll want to [setup Stripe](./stripe.md#setting-stripe-up) or you won't be able to accept payments.
