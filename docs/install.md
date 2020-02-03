# System Requirements

Simple Commerce requires that you have Statamic 3 already installed [(with all of its requirements)](https://statamic.dev/requirements) and that you setup a [MySQL database](https://laravel.com/docs/5.8/database) (this is where Commerce products, orders and customers will live).

# Install steps

1. Run `composer require doublethreedigital/simple-commerce`
2. Run `php artisan vendor:publish` and select the option `DoubleThreeDigital\SimpleCommerce\ServiceProvider`.
3. Run our database migrations and seeders to get your database setup `php artisan migrate && php artisan commerce:seed`
4. Last but not least, you'll want to [setup Stripe](./stripe.md#setting-stripe-up) or you won't be able to accept payments.
