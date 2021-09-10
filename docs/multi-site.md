---
title: Multi-site
---

Simple Commerce has first-party support for being used with Statamic's multi-site functionality.

In fact it's the way we'd recommend implementing multi-currency or multi-country sites. Each currency/country should be it's own Statamic site.

Each Statamic site you have setup in your `config/statamic/sites.php` config should also be setup in the Simple Commerce config (located at `config/simple-commerce.php`)

```php
/*
 |--------------------------------------------------------------------------
 | Sites
 |--------------------------------------------------------------------------
 |
 | For each of your Statamic sites, you can setup a new store which allows you
 | to use different currencies, tax rates and shipping methods.
 |
 */

'sites' => [
    'default' => [
        'currency' => 'GBP',

        'tax' => [
            'rate'               => 20,
            'included_in_prices' => false,
        ],

        'shipping' => [
            'methods' => [
                \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class,
            ],
        ],
    ],
],
```

When you create an additional Statamic site, just add a new key to the `sites` array, like so:

```php
'sites' => [
    'default' => [...],
    'french' => [...],
],
```

Remember that the site key will need to be the same one as used in your `sites.php` configuration file.

> 🔥 Hot Tip: Also remember that if you're wanting to use multiple sites, you'll need to [purchase & enable Statamic Pro](https://statamic.dev/licensing).

With each site you can configure the currency being used and the tax rate applied to products in the customers' cart.
