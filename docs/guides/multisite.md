---
title: 'Using with multi-site'
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
                \DuncanMcClean\SimpleCommerce\Shipping\FreeShipping::class,
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

:::tip Hot Tip
Also remember that if you're wanting to use multiple sites, you'll need to [purchase & enable Statamic Pro](https://statamic.dev/licensing).
:::

With each site you can configure the currency being used and the tax rate applied to products in the customers' cart.

## Carts

By default, Simple Commerce will give each site it's own [cart](/cart-and-orders). Meaning if you add a product to your cart on one site, it won't also be added to the cart of the user on another site.

If you're using subdirectories for your multi-sites, it's recommended you provide a different URL for each site's cart/checkout pages. So instead of all sites using `/checkout/information`, they would use `/gb/checkout/information` or `/de/checkout/information`.

It's pretty simple to do - loop through your sites & provide a route prefix.

```php
// routes/web.php

foreach (Site::all() as $site) {
    Route::prefix($site->url())->group(function () use ($site) {
        Route::statamic('/cart', 'cart', ['title' => 'Your Cart']);

        Route::statamic('/checkout/information', 'checkout.information');
        Route::statamic('/checkout/shipping', 'checkout.shipping');
        Route::statamic('/checkout/payment', 'checkout.payment');
        Route::statamic('/checkout/complete', 'checkout.complete');
    });
}
```

If you're using different domains for each of your sites, replace `Route::prefix($site->url())` with `Route::domain($site->url())` in the above code snippet.

### Using the same cart between sites

If, for whatever reason you find yourself needing to use the same cart between all of your sites. Add a `cart.single_cart` option to your config file.

```php
// config/simple-commerce.php

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
    'repository' => \DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CookieDriver::class,
    'key' => 'simple-commerce-cart',
    'single_cart' => false, // [tl! add]
],
```
