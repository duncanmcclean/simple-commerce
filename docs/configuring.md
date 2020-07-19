---
title: Configuring
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595077970
id: 74427f4f-8485-4ee9-a0ec-a729a78e59a5
is_documentation: true
nav_order: 4
---
To allow for Simple Commerce to be flexible, it gives you some configuration options so you can decide how you want to run your store.

You can find your Simple Commerce config file in `config/simple-commerce.php` in your project.

## Site configuration
Statamic has a concept of sites. Each Statamic instance can have one or more sites. For each of those sites you can use a different currency, a different tax configuration and different shipping methods.

```php
'sites' => [
    'default' => [
        'currency' => 'GBP',

        'tax' => [
            'rate' => 20,
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

Whenever you want to add another site to Simple Commerce, just change the array key from `default` to your new one. Remember to keep the site key the same between the Simple Commerce config and the Statamic config.

```
'sites' => [
    'default' => [...],
    'french' => [...],
],
```

> **Hot Tip:** Also remember that if you're wanting to use multiple sites, you'll need to [purchase & enable Statamic Pro](https://statamic.dev/licensing).

Let's walk through some of the configuration options you have with each site.

* The first option is currency, you can use a variety of different currencies in Simple Commerce. To configure one, just put in the three letter currency code and it should be picked up.

* Tax is another thing you can configure. In the default configuration, we have tax setup at 20% and we have it set so our product prices include tax. You can obviusly change this to whatever you'd like. 

* Each site can have its own set of shipping methods. A lot of sites have custom shipping rules, so we recommend you build one specifically for your site. <!-- TODO: write documentation on doing this -->

## Gateways
Simple Commerce has quite a few [built-in payment gateways](/simple-commerce/gateways), as always its something you build custom for your store.

```php
'gateways' => [
    \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
],
```

To add a gateway, just add the gateway's class name (`DummyGateway::class` syntax) as the array key and an array as the value. The value is normally used for any gateway configuration. If your gateway doesn't have any configuration options, just leave it as an empty array.

## Collections & Taxonomies
```
'collections' => [
    'products' => 'products',
    'orders' => 'orders',
    'coupons' => 'coupons',
],

'taxonomies' => [
    'product_categories' => 'product_categories',
    'order_statuses' => 'Order Statuses',
],
```

If you'd like to change the collections and handles used for certain things in Simple Commerce, we allow you to do that. Just change the appropriate value to the handle of the collection you'd like to use instead.

For example, to use a collection called `Discounts`, with a handle of `discounts` for your orders, you could configure that like this:

```
'collections' => [
    ...,
    'coupons' => 'discounts',
],
```

## Various other options
There's some small configuration options too so they're documented below.

* `cart_key` will determine the session key used for a customers' cart.