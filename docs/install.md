# Install

## System Requirements

This addon requires the latest version of Statamic 3. You should also have MySQL (or another database system) installed and configured.

## Install steps

From your terminal, run the following commands:

```shell script
composer require doublethreedigital/simple-commerce
php artisan vendor:publish --provider=DoubleThreeDigital\SimpleCommerce\ServiceProvider
php artisan migrate
php artisan commerce:seed
```
