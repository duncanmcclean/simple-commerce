# Install

## Requirements

This addon requires the latest version of Statamic 3. You should also have MySQL (or another database system) installed and configured.

You'll also want to make sure your users are stored in a database, instead of in flat files. You can [read about migrating them](https://statamic.dev/knowledge-base/storing-users-in-a-database) here.

## Install instructions

From your terminal, run the following commands:

```shell script
composer require doublethreedigital/simple-commerce
php artisan vendor:publish --provider=DoubleThreeDigital\SimpleCommerce\ServiceProvider
php artisan migrate
php artisan simple-commerce:seed
```

Also make sure to add the `IsACustomer` trait to your `User` model and add the following code:

```php
public $fields = [
	'name', 'email', 'password',
;

public function rules(): array
{
	return [
		'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
	];
}
```