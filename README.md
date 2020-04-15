# Simple Commerce
![Statamic 3.0+](https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge&link=https://statamic.com)

Simple Commerce is a perfectly simple e-commerce solution for Statamic 3. 

This repository contains the code for Simple Commerce. However, it's important to understand that you'll need a license to use this software in a production environment.

## Requirements
This addon requires the latest version of Statamic 3. You should also have MySQL (or another database system) installed and configured.

You'll also want to make sure your users are stored in a database, instead of in flat files. You can [read about migrating them](https://statamic.dev/knowledge-base/storing-users-in-a-database) here.

## Installation
From your terminal, run the following commands:

```shell script
composer require doublethreedigital/simple-commerce
php artisan vendor:publish --provider="DoubleThreeDigital\SimpleCommerce\ServiceProvider"
php artisan migrate
php artisan simple-commerce:seed
```

All customers in Simple Commerce are attached to a User model. By default in Laravel/Statamic apps, this model is `App\User`. Simple Commerce has a few things you'll need to add to your model class so it can attach relationships and process the checkout process.

The first change is that you'll need to add our `IsACustomer` trait to your model, as so:

```php
<?php

namespace App;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\IsACustomer;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use IsACustomer;

    // ...
}
```

We also require that you add a public `$fields` variable, which will be the variables that will be stored to your user model upon checkout and the `rules` method which are validation rules you wish to apply when the user submits the checkout form.

```php
<?php

namespace App;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\IsACustomer;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use IsACustomer;
    
    public $fields = [
    	'name', 'email', 'password',
    ];

    // ...

    public function rules(): array
    {
    	return [
    		'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
    	];
    }
}
```

## Limitations
As much as Simple Commerce might sound good, it has some limitations due to the way it works and because we want to keep it 'simple'. Some of the limitations include:

* Selling products in more than one currency
* Selling products with more than one tax rate

Some of these things we're thinking about adding in the future but they're not here now and some of which we'll probably never add.

## Licensing
Like Statamic, Simple Commerce is commercial software but has an open-source codebase. If you want to use Simple Commerce in production, you'll need to buy a license. 

When Statamic 3 is launched, Simple Commerce will launch on the Marketplace, until then you can buy a license by emailing [duncan@doublethree.digital](mailto:duncan@doublethree.digital).

## Resources
* [Simple Commerce Docs](./docs)
* [Simple Commerce Issues](https://github.com/doublethreedigital/simple-commerce/issues)
* [Simple Commerce Discord](https://discord.gg/P3ACYf9)
