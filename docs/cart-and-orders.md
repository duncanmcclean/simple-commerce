---
title: 'Orders'
---

## Statuses

Simple Commerce has the concept of Order & Payment statuses. They help you tell what the 'state' of an order is.

When an order is initially created, it will be a **Cart** and **Unpaid**.

After a customer has submitted the checkout form or redirected back from a third-party gateway, their order will be marked as **Placed**.

Their order will then only be marked as **Paid** when we receive confirmation from the payment gateway that a payment has taken place & been successful.

In the Control Panel, admins can then mark orders as **Dispatched** or as **Cancelled**. They may also **Refund** orders.

### Available Statuses

**Order Statuses:**

-   Cart
-   Placed
-   Dispatched (renamed from Shipped)
-   Cancelled

**Payment Statuses:**

-   Unpaid
-   Paid
-   Refunded

### Status Log

Simple Commerce also keeps track of any status changes on orders. If you view the order entry's markdown file or the order in the database, you'll see a `status_log` array with timestamps next to each of the order's timestamps.

```yaml
status_log:
    paid: '2023-02-06 20:11'
    placed: '2023-02-06 20:11'
```

## Database Orders

By default, Simple Commerce stores your orders as entries in a collection. As you get more orders, for performance reasons, you may wish to move those orders into a database.

We have details on what's involved and the process around it in our [Database Orders](/database-orders) page.

## Cart

### Cart Abandonment (Cleanup)

It can sometimes be a little frustrating to have entries for abandoned carts lying around in your Orders collection. However, if you'd prefer, Simple Commerce can be configured to automatically delete orders older than 14 days from your collection.

Simple Commerce provides a command out-of-the-box, which can be run either manually, or on demand, which will delete orders older than 14 days.

```
php please sc:purge-cart-orders
```

If you wish to run this command on a regular basis, maybe every night or every week, you can configure that in your `app/Console/Kernel.php` method, inside the `schedule` method.

```php
$schedule->command('sc:purge-cart-orders')->daily();
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
