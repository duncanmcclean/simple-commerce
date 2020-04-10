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
