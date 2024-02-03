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

**3.** Next, manually run the update scripts. This step will make changes to your config files & order data.

```
php please sc:run-update-scripts
```

:::note Note!
If you have excluded your orders from Git or you're storing your orders in a database, you will need to re-run this command after deploying Simple Commerce v6.
:::

**4.** If you're storing your orders in the database, you should uninstall & re-install Runway. It has moved to the Rad Pack:

```
composer remove doublethreedigital/runway
composer require statamic-rad-pack/runway
```

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

## Previous upgrade guides

-   [v2.2 to v2.3](/upgrade-guides/v2-2-to-v2-3)
-   [v2.3 to v2.4](/upgrade-guides/v2-3-to-v3-4)
-   [v2.4 to v3.0](/upgrade-guides/v2-4-to-v3-0)
-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)

---

[You may also view a diff of changes between v5.x and v6.0](https://github.com/duncanmcclean/simple-commerce/compare/5.x...main)
