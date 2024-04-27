---
title: 'Upgrade Guide: v5.x to v6.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple versions at once (eg. from v4 to v6). Please upgrade one step at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** As part of the v6 update, Simple Commerce's namespace has changed. You should first replace all references of `DoubleThreeDigital` in your `simple-commerce.php` config file with `DuncanMcClean`.

Then, uninstall the addon & re-install the addon using its new package name:

```sh
composer remove doublethreedigital/simple-commerce
composer require duncanmcclean/simple-commerce:^6.0
```

**2.** If you're storing orders in the database, you should also uninstall & re-install Runway:

```sh
composer remove doublethreedigital/runway
composer require statamic-rad-pack/runway
```

**3.** Update scripts should have ran during the `composer` commands. However, if they didn't or you ran into errors running them, try to run them manually instead:

```sh
php please sc:run-update-scripts
```

:::note Note!
If you have excluded your orders from version control or you're storing your orders in a database, you **will** need to re-run this command after deploying these changes.
:::

**4.** Finally, to ensure nothing's cached, clear Laravel's route & view cache:

```sh
php artisan route:clear
php artisan view:clear
```

**5.** And... that's you updated! It's possible you may need to make some manual changes. Please review this guide for information on changes which may effect your site.

**Please test locally before deploying to production!**

## Changes

### High: Namespace has changed

Simple Commerce's namespace has changed from `DoubleThreeDigital` to `DuncanMcClean`.

During the update steps, you will have updated any references in your config file. However, if you have references to `DoubleThreeDigital` anywhere in your project, you will need to update them to reference the new namespace instead.

For example:

```php
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway; // [tl! remove]
use DuncanMcClean\SimpleCommerce\Gateways\BaseGateway; // [tl! add]
```

### High: References to gateways & shipping methods have changed

Previously, when referencing a Payment Gateway or Shipping method, for example in order data, its FQCL (fully-qualified class name) would have been used, like this:

```yaml
shipping_method: DuncanMcClean\SimpleCommerce\Shipping\FreeShipping
```

However, with v6, they're now referenced by handles instead:

```yaml
shipping_method: free_shipping
```

This change may require you to make some code changes. Please read through the following steps:

#### In your orders

When running the `php please sc:run-update-scripts` command, your orders should be automatically updated to reference handles instead. You don't need to update order data manually.

#### In your templates

If you're manually referencing class names anywhere in your templates, you should update them to instead reference the handles. Your code editor's "Find All & Replace" feature is helpful for this:

* `{{ class }}` -> `{{ handle }}`
* `{{ formatted_class }}` -> `{{ handle }}`

#### In your config files

If you have a [default shipping method configured](/shipping#content-default-shipping-method), you should reference the shipping method's handle, instead of its class name.

```php
'sites' => [
    'default' => [
        ...
 
        'shipping' => [
            'default_method' => \DuncanMcClean\SimpleCommerce\Shipping\FreeShipping::class, // [tl! remove]
            'default_method' => 'free_shipping', // [tl! add]
 
            'methods' => [
                \DuncanMcClean\SimpleCommerce\Shipping\FreeShipping::class => [],
            ],
        ],
    ],
],
```

### High: Runway v6

If you're storing orders & customers in the database, you should also follow the [Runway v6 upgrade guide](https://runway.duncanmcclean.com/upgrade-guides/v5-x-to-v6-0).

### High: Database Migrations

If you're storing orders in the database, you will need to run the necessary migrations, both locally and after deploying to other environments:

```sh
php artisan migrate
```

### Medium: The "Overview" page has been removed

The "Overview" page has been removed in Simple Commerce v6, in favour of Dashboard Widgets. To configure Simple Commerce widgets, please review the [Control Panel](/control-panel#content-widgets) page.

### Medium: The "Enabled" status has been removed from Coupons

The "Enabled" status toggle has been removed from coupons, in favour of the Start/End dates.

As part of the update process, Simple Commerce will attempt to update your existing disabled coupons and set an end date on them so they're no longer usable.

### Medium: Changes to the `statusLog` method on Orders

The `statusLog` method no longer accepts passing a status. Instead, you should query the status log for the event you're after, then get the `->last()` item in the collection.

```php
// Previously
$order->statusLog('paid');

// Now
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;

$order->statusLog()
    ->where('status', OrderStatus::Placed)
    ->map(fn ($statusLogEvent) => $statusLogEvent->data()->timestamp)
    ->last();
```

### Medium: Digital Products are now supported in Core

Previously, you needed to install a separate addon to support Digital Products. With v6, this addon has been merged into Simple Commerce Core. It works *mostly* the same as the addon did but there are a few notable changes and things you should do as part of the update process:

#### Uninstall Digital Products addon

Now the addon is no longer needed, you may uninstall it:

```
composer remove doublethreedigital/sc-digital-products
```

#### Notifications config

The namespace for the `DigitalDownloadsReady` notification has changed. You should update the reference to it in your `simple-commerce.php` config file:

```php
'notifications' => [
    'digital_download_ready' => [
        \DuncanMcClean\DigitalProducts\Notifications\DigitalDownloadsNotification::class => [ // [tl! remove]
        \DuncanMcClean\SimpleCommerce\Notifications\DigitalDownloadsNotification::class => [ // [tl! add]
            'to' => 'customer',
        ],
    ],
],
```

#### URLs have changed

As the code has been merged into the main Simple Commerce addon, the URLs have changed to reflect that. This means any download URLs sent prior to updating will 404 and if you're using the addon's License Verification API endpoint, you will need to reference the new URL:

```
https://example.com/!/simple-commerce/digital-products/verification
```

#### Enabling Digital Products

With the addon, a "Digital Products" tab was forced onto your product blueprint which once toggled would show the other digital product related fields. This has changed a little in v6 - you now have to toggle the 'Product Type' field instead:

![Product Type toggle](/img/simple-commerce/product-type-toggle.png)

As part of the update process, all of your products should be updated so `Product Type` is set to `Digital`.

#### Download History

Previously, the "Download History" functionality provided by the addon was opt-in. This feature is now always enabled.

### Low: The `all` method on repositories has changed

If you have any custom code which calls `Order::all()`, `Product::all()` or `Customer::all()`, you may need to adjust your code.

The `all` method on these repositories now returns a `Collection` of `Order`/`Product`/`Customer` objects, rather than returning an array of `Entry` or Eloquent model objects.

This saves you needing to `find` the order/product/customer to use any of Simple Commerce's helper methods.

### Low: Repository `find` methods no longer throw exceptions

The `find` method on the `Order`/`Product`/`Customer` facades will no longer throw an exception when the entry or model can't be found. Instead, it'll now return `null`.

You can use the `findOrFail` methods instead if you wish exceptions to be thrown.

### Low: Gateway methods on the `Order` class have changed

Both the `gateway` and `currentGateway` methods on orders have been replaced. If you were calling these methods anywhere in your code, you should update your code to use the new `gatewayData` method:

```php
// Get the gateway's name & handle
$order->gatewayData()->gateway()->name();
$order->gatewayData()->gateway()::handle();

// Get the gateway / payment info
$order->gatewayData()->data();

// Get any refund data
$order->gatewayData()->refund();
```

## Previous upgrade guides

-   [v2.2 to v2.3](/upgrade-guides/v2-2-to-v2-3)
-   [v2.3 to v2.4](/upgrade-guides/v2-3-to-v3-4)
-   [v2.4 to v3.0](/upgrade-guides/v2-4-to-v3-0)
-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)

---

[You may also view a diff of changes between v5.x and v6.0](https://github.com/duncanmcclean/simple-commerce/compare/5.x...main)
