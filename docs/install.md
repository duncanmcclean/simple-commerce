# Installation

> If you want to get started without having to do all this install stuff, [consider using our starter kit](https://github.com/doublethreedigital/simple-commerce-starter) instead.

## Requirements

Simple Commerce requires that you're running the latest version of the Statamic 3 beta and you'r running PHP 7.2 or higher. MySQL (or an alternative) is also required and should be configured beforee installing Simple Commerce.

You'll also want to make sure your users are stored in a database, instead of in flat files. You can [read about migrating them](https://statamic.dev/knowledge-base/storing-users-in-a-database) here.

## Install instructions

1. Install Simple Commerce through Composer - `composer require doublethreedigital/simple-commerce`
2. Then run `php please simple-commerce:install` which will migrate your database, publish vendor assets and seed your database.
3. Done! :tada:

## The User Model

Simple Commerce attaches things like orders and transactions to a customer. We don't provide a Customer model out of the box and we recommend for you to use the default `User` model that gets packaged with new Laravel/Statamic apps.

Simple Commerce needs to add a few things to the model so that things work properly when a customer goes through the checkout process.

When following the install process, using the `simple-commerce:install` command you will have been asked if you want your `User` model to be replaced by a stub we provide. If you didn't want for that to happen, you'll need to do it manually. Don't worry, it's not too much.

The first thing you'll need to do is add our `IsACustomer` trait to your model, like so:

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

Secondly, we ask that you add a protected `$fields` array and a `rules` function that contains some Laravel validation rules. These will be used to validate various customer fields and to make sure certain fields get saved to the `User` model.

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

And that's it! Everything should work just fine.
