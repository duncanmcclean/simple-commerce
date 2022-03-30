---
title: "Cart & Orders"
---

Under the hood, Carts and Orders are really the same thing. They live under the same collection, use the same blueprint.

Often, when a cart is being mentioned, it's just referring to an unpaid order (defined by the `is_paid` boolean). If `is_paid` is true, it'll be refered to as an order.

## Cart

### Cart Abandonment (Cleanup)

It can sometimes be a little frustrating to have entries for abandoned carts lying around in your Orders collection. However, if you'd prefer, Simple Commerce can be configured to automatically delete orders older than 14 days from your collection.

Simple Commerce provides a command out-of-the-box, which can be run either manually, or on demand, which will delete orders older than 14 days.

```
php please sc:cart-cleanup
```

If you wish to run this command on a regular basis, maybe every night or every week, you can configure that in your `app/Console/Kernel.php` method, inside the `schedule` method.

```php
$schedule->command('sc:cart-cleanup')
  ->daily();
```

For more documentation on command scheduling, please [review the Laravel documentation](https://laravel.com/docs/master/scheduling#scheduling-artisan-commands).

### Cart Drivers

Simple Commerce provides a concept of 'Cart Drivers'. A cart driver essentially is the method on which you store the customer's current Cart ID.

Two cart drivers are provided as standard: the cookie driver & the session driver. I'd generally recommend using the cookie driver, as it has a longer lifetime compared to a session, meaning the customer can come back later without losing their cart.

#### Configuring

```php
/*
 |--------------------------------------------------------------------------
 | Cart
 |--------------------------------------------------------------------------
 |
 | Configure the Cart Driver in use on your site. It's what stores/gets the
 | Cart ID from the user's browser on every request.
 |
 */

'cart' => [
  'repository' => \DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CookieDriver::class,
  'key' => 'simple-commerce-cart',
],
```

In your `config/simple-commerce.php` file, you can configure the cart driver class being used and the 'key' used by the driver.

#### Building your own cart driver

If you need to, you can create your own cart driver. It's a relativly simple thing to do: create your class, implement a couple of methods and hey presto!

Below is a boilerplate driver class to get you started:

```php
<?php

namespace App\CartDrivers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;

class YourDriver implements CartDriver
{
    public function getCartKey(): string
    {
        //
    }

    public function getCart(): Order
    {
        //
    }

    public function hasCart(): bool
    {
        //
    }

    public function makeCart(): Order
    {
        //
    }

    public function getOrMakeCart(): Order
    {
        //
    }

    public function forgetCart()
    {
        //
    }
}
```

## Orders

### Order Numbers

When an order is created, a unique order number will be assigned. It'll often be formatted like so: `#1234`.

By default, order numbers will start at `#2000` and will continue endlessly. If you wish for order numbers to start at say, `#5000`, you can configure that in your `config/simple-commerce.php` config file.

```php
<?php

return [
	...

   /*
    |--------------------------------------------------------------------------
    | Order Number
    |--------------------------------------------------------------------------
    |
    | If you want to, you can change the minimum order number for your store. This won't
    | affect past orders, just ones in the future.
    |
    */

    'minimum_order_number' => 2000,
];
```

### Order Blueprints

Like anything else, you're free to make changes to your order blueprint. Simple Commerce will use the default blueprint for the orders collection every time.

When making changes however, please try to keep the field handles the same as what we use, otherwise data won't be displayed correctly in the Control Panel.
