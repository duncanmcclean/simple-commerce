---
title: 'Upgrade Guide: v5.x to v6.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple versions at once (eg. from v4 to v6). Please upgrade one step at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** In your `composer.json` file, update the `doublethreedigital/simple-commerce` version constraint:

```json
"doublethreedigital/simple-commerce": "^6.0"
```

**2.** Then run:

```
composer update doublethreedigital/simple-commerce --with-dependencies
```

**3.** If you're storing your orders in the database, you should uninstall & re-install Runway. It has moved to the Rad Pack:

```
composer remove doublethreedigital/runway
composer require statamic-rad-pack/runway
```

**4.** Next, manually run the update scripts. This step will make changes to your config files & order data.

```
php please sc:run-update-scripts
```

:::note Note!
If you have excluded your orders from Git or you're storing your orders in a database, you will need to re-run this command after deploying Simple Commerce v6.
:::

**5.** You may also want to clear your route & view caches:

```
php artisan route:clear
php artisan view:clear
```

**6.** Simple Commerce will have attempted upgrading some things for you (like config files, blueprints, etc). However, it's possible you'll need to make some manual changes. Please review this guide for information on changes which may effect your site.

**Please test locally before deploying to production!**

## Changes

### High: Runway v6

If you're storing orders & customers in the database, you should also follow the [Runway v6 Upgrade Guide](https://runway.duncanmcclean.com/upgrade-guides/v5-x-to-v6-0).

### High: Database Migrations

If you're storing orders in the database, you will need to run the migrations, both locally & when deploying to any other environments:

```
php artisan migrate
```

## High: References to gateways & shipping methods in orders have changed

Previously, when referencing a Payment Gateway or Shipping Method in an order, Simple Commerce would use its fully-qualified class name, like so:

```yaml
shipping_method: DoubleThreeDigital\SimpleCommerce\Shipping\FreeShipping
```

However, v6 changes this so Payment Gateways & Shipping Methods are now referenced by their handles:

```yaml
shipping_method: free_shipping
```

Simple Commerce will automatically update your orders when you run the `sc:run-update-scripts` command.

If you're manually referencing gateway / shipping method class names anywhere, you should instead reference the handle. To determine if you're referencing class names, search for `{{ class }}` in your site's shipping & checkout pages and change any instances to `{{ handle }}`.

### Medium: The `all` method on repositories has changed

If you have any custom code which calls `Order::all()`, `Product::all()` or `Customer::all()`, you may need to adjust your code.

The `all` method on these repositories now returns a `Collection` of `Order`/`Product`/`Customer` objects, rather than returning an array of `Entry` or Eloquent model objects.

This saves you needing to `find` the order/product/customer to use any of Simple Commerce's helper methods.

### Medium: The "Overview" page has been removed

The "Overview" page has been removed in Simple Commerce v6, in favour of Dashboard Widgets. To configure Simple Commerce widgets, review the [Control Panel](/control-panel#content-widgets) page.

### Medium: Order Gateway methods have changed

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

### Medium: The "Enabled" status has been removed from Coupons

The "Enabled" status toggle has been removed from coupons, in favour of the Start/End dates.

As part of the update process, Simple Commerce will attempt to update your existing disabled coupons and set an end date on them so they're no longer usable.

### Medium: Changes to the `statusLog` method on Orders

The `statusLog` method no longer accepts passing a status. Instead, you should query the status log for the event you're after, then get the `->last()` item in the collection.

```php
// Previously
$order->statusLog('paid');

// Now
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;

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
        \DoubleThreeDigital\DigitalProducts\Notifications\DigitalDownloadsNotification::class => [ // [tl! remove]
        \DoubleThreeDigital\SimpleCommerce\Notifications\DigitalDownloadsNotification::class => [ // [tl! add]
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

## Previous upgrade guides

-   [v2.2 to v2.3](/upgrade-guides/v2-2-to-v2-3)
-   [v2.3 to v2.4](/upgrade-guides/v2-3-to-v3-4)
-   [v2.4 to v3.0](/upgrade-guides/v2-4-to-v3-0)
-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)

---

[You may also view a diff of changes between v5.x and v6.0](https://github.com/duncanmcclean/simple-commerce/compare/5.x...main)
