# System Requirements

Simple Commerce requires that you have Statamic 3 already installed [(with all of its requirements)](https://statamic.dev/requirements) and that you setup a [MySQL database](https://laravel.com/docs/5.8/database) (this is where Commerce products, orders and customers will live).

# Install steps

While Simple Commerce is in beta, you'll need to follow some extra steps to get everything setup right. Once we're on the marketplace, it'll be much easier.

## While in beta

1. Clone this repository to `./addons/doublethreedigital/simplecommerce` - `git clone git@github.com:damcclean/simple-commerce.git addons/doublethreedigital/simplecommerce`
2. Run `composer install` inside the `./addons/doublethreedigital/simplecommerce` folder.
3. In your site's main `composer.json` file, add the following few lines:

```json
  "require": {
      "doublethreedigital/simple-commerce": "dev-master"
  },

  "repositories": [
        {
            "type": "path",
            "url": "addons/doublethreedigital/simple-commerce"
        }
    ]
```

4. Run `composer install && composer update`
5. Run `php artisan vendor:publish` and select the option `DoubleThreeDigital\SimpleCommerce\ServiceProvider`.
6. You'll also need to run our database migrations and seeders to get your database setup. `php artisan migrate && php artisan db:seed`
7. Last but not least, you'll want to [setup Stripe](./stripe.md#setting-stripe-up) or you won't be able to accept payments.

## When we're on the Marketplace

1. Run `composer require doublethreedigital/simple-commerce`
2. Run `php artisan vendor:publish` and select the option `DoubleThreeDigital\SimpleCommerce\ServiceProvider`.
3. Run our database migrations and seeders to get your database setup `php artisan migrate && php artisan db:seed`
4. Last but not least, you'll want to [setup Stripe](./stripe.md#setting-stripe-up) or you won't be able to accept payments.
