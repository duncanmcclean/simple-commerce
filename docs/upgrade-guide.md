---
title: Upgrade Guide
---

## Overview

When upgrading, read through this guide to see if there's anything you may need to change. A few of the big common changes will be done automatically (and will be marked as 'Automated' throughout the guide) but there will likely be some manual steps you'll need to take.

In your `composer.json` file, update the `doublethreedigital/simple-commerce` version constraint:

```json
"doublethreedigital/simple-commerce": "3.0.*"
```

Then run:

```
composer update doublethreedigital/simple-commerce --with-dependencies
```

## Changes

### High: Changes around the Data APIs (Partially automated)

This is probably **the largest change** around how Simple Commerce works. If you've written any kind of custom code that deals with an `Order`, `Product`, etc, I'd recommend you test your code to ensure it's compatible with v3.0.

As part of the changes, a small configuration change was needed. This change should be **automated** for you. Instead of seeing `driver` keys inside of the 'content drivers' array, you should see `repository` keys.

```php
'content' => [
    'coupons' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Coupons\EntryCouponRepository::class,
        'collection' => 'coupons',
    ],

    'customers' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository::class,
        'collection' => 'customers',
    ],

    'orders' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository::class,
        'collection' => 'orders',
    ],

    'products' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Products\EntryProductRepository::class,
        'collection' => 'products',
    ],
],
```

You'll also find that [the 'interfaces'](https://github.com/doublethreedigital/simple-commerce/tree/3.0/src/Contracts) for each of these Data APIs have been rewritten.

In addition to these changes, you should check any custom code you've written is still compatible with Simple Commerce's updated APIs. Here's a few common examples of patterns in v2.4 and what they look like now in v3.0.

#### Creating products/customers/orders/coupons

**Previously:**

```php
$order = Order::create([
    'items' => [
        [
            'product' => 'abc',
            'quantity' => 1,
            'total' => 1255,
        ],
    ],
    'items_total' => 1255,
    'gift_note' => 'This is a very special note.',
]);
```

**Now:**

```php
$order = Order::make()
    ->lineItems([
        [
            'product' => 'abc',
            'quantity' => 1,
            'total' => 1255,
        ],
    ])
    ->itemsTotal(1255)
    ->data([
        'gift_note' => 'This is a very special note.',
    ]);

$order->save();
```

A lot of 'things' on Data Models are now properties which can be modified using fluent getter/setters. Anything outside of a property can be set using `->data()`, `->set()` or `->merge()`. We're now using the same pattern for making/saving as Statamic itself.

#### Getting grand total from an order

**Previously:**

```php
$order = Order::find('123');

$order->get('grand_total');
```

**Now:**

```php
$order = Order::find('123');

$order->grandTotal();
```

#### Getting the original entry

**Previously:**

```php
$order = Order::find('123');

$order->entry();
```

**Now:**

```php
$order = Order::find('123');

$order->resource();
```

### High: Field Whitelisting (Partially automated)

To improve the security of your site, we've introduced a 'whitelist' for the fields that will be saved from Simple Commerce's front-end forms.

Previously, Simple Commerce would save anything provided in a request (for example: on the request to add a product to the cart) to your order entry.

Now, SC will only save the request data from the fields you've whitelisted in the Simple Commerce config.

Simple Commerce has **partially automated** this upgrade step for you. Upon upgrade, a `field_whitelist` key will be added to your `simple-commerce.php` config file.

It will have pulled in any fields from your orders that aren't 'reserved' (eg. SC presumes you probably don't want the `is_paid` field to be fillable).

### High: Updates to Shipping Methods

Simple Commerce now allows for passing configuration arrays for shipping methods. However, for this to work, shipping methods must be updated to extend upon the `BaseShippingMethod` class provided by Simple Commerce.

```php
<?php

namespace App\ShippingMethods;

use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Shipping\BaseShippingMethod;

class FirstClass extends BaseShippingMethod implements ShippingMethod
{
    //
}
```

If you wish to start passing in config variables to your shipping methods, you may do it like so:

```php
'sites' => [
    'default' => [
        ...

        'shipping' => [
            'methods' => [
                \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class => [
                    'config' => 'setting',
                    'foo' => 'bar',
                ],
            ],
        ],
    ],
],
```

And inside the shipping method, you may do `$this->config()->get('key')` to get a specific config value.

### Medium: Order Emails

Previously, Simple Commerce would send a fairly basic email with a PDF receipt attached.

The library used to generate the PDF receipts (DomPDF) doesn't support new CSS features and was limiting in its available functionality. In v3, the receipr PDF has been removed from order emails. Instead, email body's contain a table with the order line items, along with any customer information which is displayed below.

![](/img/simple-commerce/order-email-example.png)

If you wish to continue with the previous behavior, you may create a copy of the [notifications from v2.4](https://github.com/doublethreedigital/simple-commerce/tree/2.4/src/Notifications) and put them in your `app` folder.

Then, in your config file, you'd adjust the notification class used to match your app one. Be sure to manually require the DomPDF dependency, as this is no longer required for you by Simple Commerce.

```
composer require dompdf/dompdf
```

### Medium: Order Confirmation page

Although not technically a breaking change, it's worth noting as it may help to cleaup your code.

On the 'Order Confirmation' page (the one after checking out), you'd previously have to use the `{{ session:cart }}` tag in order to access the previous cart. However, now you may use Simple Commerce's cart tags like normal.

**Previously:**

```antlers
{{ session:cart }}
    Thanks for your order ({{ title }}), {{ customer:title }}
{{ /session:cart }}
```

**Now:**

```antlers
{{ sc:cart }}
    Thanks for your order ({{ title }}), {{ customer:title }}
{{ /sc:cart }}
```

### Medium: Gateway fieldtype

Included in Simple Commerce v3 is a 'Gateway Fieldtype' which allows you to view the gateway used for a specific order, along with information around the payment itself. New sites will get the Gateway fieldtype by default but it's recommended you also add it to your existing order blueprint.

1. Go into the Control Panel, click into 'Blueprints'
2. Edit your order blueprint, add the 'Gateway' fieldtype. Remember to use the handle of `gateway`, otherwise it won't work.

Now, when you view orders, you'll see information around the particular payment.

> **Note:** When using the Stripe Gateway, only new orders will show any payment information, due to some required data we were not previously storing.

### Low: Higher System Requirements

Simple Commerce v3 requires you to be using PHP 8.0 (and above), along with Laravel 8 (and above) and Statamic 3.3. Adjusting the system requirements encourages developers to stay up to date and means Simple Commerce can take advantage of new features.

### Low: Order Numbers (Automated)

In the past, order numbers would be stored as part of the title on Order entries.

However, SC v3 has taken advantage of a Statamic feature called ['title formats'](https://statamic.dev/collections#titles). This means we store the order number in it's own field, `order_number` (hidden field, added during upgrade).

Then, Simple Commerce will configure the title format to be like so: `#xxxx`.

### Low: Updated namespaces for `Currency`/`Country`/`Region` classes

If you were previously referencing any of these classes, you should update your references to their new namespaces:

- `DoubleThreeDigital\SimpleCommerce\Support\Currency` -> `DoubleThreeDigital\SimpleCommerce\Currency`
- `DoubleThreeDigital\SimpleCommerce\Support\Country` -> `DoubleThreeDigital\SimpleCommerce\Country`
- `DoubleThreeDigital\SimpleCommerce\Support\Regions` -> `DoubleThreeDigital\SimpleCommerce\Regions`

## Running into an issue upgrading?

Like I say, quite a lot has changed between v2.4 and v3.0 so if you're running into issues upgrading, please either [open a GitHub Issue](https://github.com/doublethreedigital/simple-commerce/issues/new/choose) or [send me an email](mailto:help@doublethree.digital).

## Previous upgrade guides

- [v2.2 to v2.3](https://github.com/doublethreedigital/simple-commerce/blob/2.3/docs/upgrade-guide.md)
- [v2.3 to v2.4](https://github.com/doublethreedigital/simple-commerce/blob/2.4/docs/upgrade-guide.md)

---

[You may also view a diff of changes between v2.4 and v3.0](https://github.com/doublethreedigital/simple-commerce/compare/2.4...3.0)
