---
title: 'Upgrade Guide: v4.x to v5.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple versions at once (eg. from v3 to v5). Please upgrade one step at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** In your `composer.json` file, update the `doublethreedigital/simple-commerce` version constraint:

```json
"doublethreedigital/simple-commerce": "^5.0"
```

**2.** Then run:

```
composer update doublethreedigital/simple-commerce --with-dependencies
```

**3.** You may also want to clear your route & view caches:

```
php artisan route:clear
php artisan view:clear
```

**4.** Simple Commerce will have attempted upgrading some things for you (like config files, blueprints, etc). However, it's possible you'll need to make some manual changes. Please review this guide for information on changes which may effect your site.

**Please test locally before deploying to production!**

## Changes

### High: Order & Payment Statuses

Previously, orders had status fields like `is_paid` and `is_shipped` to determine the "status" of orders.

In v5.0, Simple Commerce has introduced two new concepts: Order Statuses & Payment Statuses. One marks the status of the order (Cart, Placed, Dispatched, Cancelled) and the other marks the status of the payment (Unpaid, Paid, Refunded).

They'll be saved in your order like so:

```yaml
order_status: placed
payment_status: paid
```

When you run the `composer update` command to upgrade to v5.0, Simple Commerce will attempt to migrate your existing orders to the new format.

In addition, it will add the new status fields to your order blueprint & remove the old ones.

If you previously had notifications configured to send on the `order_shipped` event, Simple Commerce should have updated the event name to `order_dispatched`.

However, it's worth noting that depending on your setup, **you may need to take some manual steps**:

#### Flat-file orders

:::note Note!
Before running Simple Commerce's migration command on production, please take a backup of your Orders collection so you can rollback if needed.
:::

If you store your orders as entries AND you're Git-ignoring those entries, you will need to run the migration script manually after deploying the Simple Commerce update.

```
php please sc:migrate-order-statuses
```

It will update order entries to use the new format for statuses.

#### Database orders

:::note Note!
Before running Simple Commerce's migration command on production, please take a backup of your `orders` database table so you can rollback if needed.
:::

You will need to run the migration script manually after deploying the Simple Commerce update.

```
php please sc:migrate-order-statuses
```

It will generate a migration to add two new columns to the `orders` table: `order_status` and `payment_status`. It'll then run these migrations for you.

It'll then work through the orders in your database and set the status columns. After this migration has run, the 'old' status columns will be set to `null`.

You may create your own migration to remove the 'old' columns after deploying if you wish (`is_paid`, `is_shipped`, `is_refunded`).

### High: Variables are passed differently into the `{{ sc:checkout }}` form

Sometimes, you could run into issues with the `{{ sc:checkout }}` form where variables for one gateway would leak over into the other because they were sharing the same "space" for their variables.

In v5, we've slightly changed the format variables are passed into the `{{ sc:checkout }}` form to prevent this from happening.

Due to the format changing, you may need to update code in your checkout form.

1. If you're getting any variables that come from gateways, you should get it from that gateway's array:
    - `{{ client_secret }}` -> `{{ stripe:client_secret }}`
2. If you're getting config values for a gateway, you should get it from that gateway's config array:
    - `{{ gateway-config:key }}` -> `{{ stripe:config:key }}`
3. If you're getting the callback URL for a gateway, you should get it from that gateway's array:
    - `{{ callback_url }}` -> `{{ stripe:callback_url }}`

### High: Stripe Gateway now defaults to Payment Elements integration

By default, Simple Commerce now takes advantage of Stripe's Payment Elements integration. This allows for customers to pick the payment methods that work for them - the payment methods are localised based on the user's location.

Any existing sites though will be using Card Elements, which just gives you a basic card payment form. To continue using in Card Elements, you'll need to specify a `mode` in your Gateways config.

```php
// config/simple-commerce.php

/*
|--------------------------------------------------------------------------
| Payment Gateways
|--------------------------------------------------------------------------
|
| This is where you configure the payment gateways you wish to use across
| your site. You may configure as many as you like.
|
| https://simple-commerce.duncanmcclean.com/gateways
|
*/

'gateways' => [
    \DuncanMcClean\SimpleCommerce\Gateways\Builtin\StripeGateway::class => [ // [tl! focus]
        'key' => env('STRIPE_KEY'), // [tl! focus]
        'secret' => env('STRIPE_SECRET'), // [tl! focus]
        'mode' => 'card_elements',  // [tl! add] [tl! focus]
    ], // [tl! focus]

    \DuncanMcClean\SimpleCommerce\Gateways\Builtin\DummyGateway::class => [
        'display' => 'Card',
    ],
],
```

### Medium: Support for PHP 8.1 has been dropped

Simple Commerce has dropped support for PHP 8.1, leaving only PHP 8.2 supported.

### Medium: Support for Statamic 3.3 & 3.4 has been dropped

Simple Commerce has dropped support for Statamic 3.3 and 3.4, leaving only Statamic 4.0 and above supported.

To upgrade to Statamic 4.0, you should follow the steps outlined in the official [Upgrade Guide](https://statamic.dev/upgrade-guide/3-4-to-4-0).

### Medium: New `order_number` column for database orders

Previously, when storing orders in the database, Simple Commerce has used the `id` column as order numbers. This meant order numbers started in the single digits and it wasn't possible to set a minimum order number (eg. 1000) like you can with entry orders.

In v5, Simple Commerce has introduced a separate `order_number` column. During the update process, a migration will be published & your existing orders will be updated.

### Medium: Changes to Gateway API

As part of improving the developer experience in Simple Commerce, there has been some changes to the Gateways API. If you're using a custom gateway in your project, you will need to make some manual changes, as detailed below.

If you're using a built-in gateway, you don't need to worry about these changes.

#### `prepare`

-   The parameters for this method have changed. It now accepts `$request` and `$order` parameters, instead of a `Prepare` object.
-   This method no longer returns a `Response`, instead it simply returns an array.

#### `purchase`

-   This method has been renamed `checkout`
-   The parameters for this method have changed. It now accepts `$request` and `$order` parameters, instead of a `Prepare` object.
-   This method no longer returns a `Response`, instead it simply returns an array.

#### `purchaseRules`

-   This method has been renamed `checkoutRules`

#### `purchaseMessages`

-   This method has been renamed `checkoutMessages`

#### `getCharge`

-   This method has been removed. It wasn't used by Simple Commerce anywhere so it didn't make sense to keep it.
-   If you use it inside your gateway's class, you can keep it.

#### `refundCharge`

-   This method has been renamed `refund`
-   This method now returns an `array`. The contents of which will be saved to the order's gateway data for future reference.

#### `paymentDisplay`

-   This method has been renamed `fieldtypeDisplay`

### Low: Control Panel Nav

Previously, Simple Commerce would use custom JavaScript to re-order your Control Panel navigation to place the 'Simple Commerce' section under 'Content'.

However, after the introduction of the [CP Nav Customiser](https://statamic.dev/customizing-the-cp-nav#accessing-cp-nav-preferences) in Statamic 3.4, you may now customise the order of the Control Panel Nav as you'd like.

For continuity, we've set your 'default preferences' for the CP Nav to how it was before. However, you're free to change this.

### Low: `ReceiveGatewayWebhook` event has been renamed

If you are listening to the `ReceiveGatewayWebhook` anywhere in your site, you should instead listen for `GatewayWebhookReceived`. The event has been renamed

## Previous upgrade guides

-   [v2.2 to v2.3](/upgrade-guides/v2-2-to-v2-3)
-   [v2.3 to v2.4](/upgrade-guides/v2-3-to-v3-4)
-   [v2.4 to v3.0](/upgrade-guides/v2-4-to-v3-0)
-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)

---

[You may also view a diff of changes between v4.x and v5.0](https://github.com/duncanmcclean/simple-commerce/compare/4.x...main)
