# Configuring

Simple Commerce provides a configuration file that lets you configure how your store is run, including what currencies are used and the payment gateways available to customers.

After you've installed Simple Commerce, you should find a `simple-commerce.php` file in your projects' `config` directory. That's your configuration file.

```
<?php

return [
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

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
    ],

    'cart_key' => 'simple-commerce-cart',
];
```

## Site configuration
Simple Commerce allows you to run multiple e-commerce stores under a single Statamic instance by making use of Statamic's multi-site functionality. You can learn more about multi-site on the [Statamic documentation](https://statamic.dev/multi-site#content).

> **Hot Tip:** To use multi-sites in Statamic, you'll need to purchase and enable Statamic Pro.

By default, with all Statamic sites, there's a `default` site already setup (you don't need Pro for a single site). We automatically ship with some basic configuration.

Each site can have it's own set of currency, tax and shipping settings.

The value of `currency` should be the three letter code for the currency you wish to use. This would be `GBP` for Great British Pound or `USD` for the United States Dollar.

You'll also need to configure tax for your store. You can configure the tax rate to used and if prices in your store already include tax, in which case, we don't add it to order totals.

You can also configure different shipping methods for use in your store, you can read more about them over here. Essentially, all you need to do is include the class name like this and you'll have registered a shipping method.

```
'shipping' => [
  'methods' => [
    \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class,
  ],
],
```

## Gateways
We also allow you to choose which payment gateways you'd like to use in your store. Feel free to create your own or provide ones that Simple Commerce provides out of the box.

Again, these can be configured with the `Something::class` syntax.

## And the other settings...

* `cart_key` - this determines the session key used for storing the customer's cart ID
