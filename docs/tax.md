---
title: Tax
---

## Overview

Simple Commerce includes two *tax engines* out of the box:

* **Basic Tax Engine:** This allows you to have a flat tax rate that's applied to all products & customers.
* **Standard Tax Engine:** This allows you to associate different tax rates to different products & based on where the customer is located.

Simple Commerce then calculates the tax amount for each line item on an order.

## Basic Tax Engine

To enable the Basic Tax Engine, your config should look like this:

```php
// config/simple-commerce.php

'tax_engine' => \DuncanMcClean\SimpleCommerce\Tax\BasicTaxEngine::class,

'tax_engine_config' => [
    'rate'               => 20,
    'included_in_prices' => false,
],
```

As explained above, the Basic Tax Engine simply lets you define a flat tax rate which will be applied to all products & customers. The config allows you to define the tax rate (20% in the example) and whether or not tax has already been included in the product prices.

If you have a product which is exempt from tax, you may add a Toggle field to your Product blueprint, called `exempt_from_tax`. Then, you may turn the toggle on for the product.

### Shipping Tax

If you wish to enable tax for shipping costs, simply flick the toggle in your Simple Commerce config:

```php
// config/simple-commerce.php

'tax_engine_config' => [
    // ...
    'shipping_taxes' => true,
],
```

Now, the tax rate you have set for all products will also be applied to shipping costs.

## Standard Tax Engine

The Standard Tax Engine is enabled by default in new Simple Commerce sites. You may enable it if you're on an older site like so:

```php
// config/simple-commerce.php

'tax_engine' => \DuncanMcClean\SimpleCommerce\Tax\Standard\TaxEngine::class,

'tax_engine_config' => [
    'address' => 'billing',

    'behaviour' => [
        'no_address_provided' => 'default_address',
        'no_rate_available' => 'prevent_checkout',
    ],

    'default_address' => [
        'address_line_1' => '',
        'address_line_2' => '',
        'city' => '',
        'region' => '',
        'country' => '',
        'zip_code' => '',
    ],
],
```

There's three main concepts you'll want to be familuar with before you begin:

-   Tax Categories - These are the 'types' of tax you may need to apply. For example: you may have one category for 'Standard Tax' and another for 'Zero Tax'. You can then apply these categories to your products.
-   Tax Zones - These are the 'areas' where you wish to apply certain tax rates to. For example: you may want to restrict a tax rate to being UK only. You're able to select a country and a region inside of it.
-   Tax Rates - These are where you define the tax rate for a Category/Zone combination. For example: you could apply 20% tax if it's a product in the 'Standard Tax' category and the customer is located inside the UK

After enabling the tax engine, you will also want to go ahead and setup your Rates, Categories and Zones. Each of these have sections in the Control Panel.

> If you'd like your client (or other non-super user) to be able to access these pages, you may give them access via [Permissions](https://statamic.dev/users#permissions).

### Shipping Tax

Simple Commerce will automatically create a 'Default - Shipping' tax category when you enable the Standard Tax Engine.

This tax category will be used when Simple Commerce is figuring out what tax (if any) should be applied to shipping costs.

Similar to products, you may associate tax rates with the shipping category. Then tax will be applied to shipping in the user's cart.

### Edge Cases

#### No address provided

If the order doesn't have an address when tax is being calculated, Simple Commerce will be unable to apply one of your Tax Rates.

In this case, you can either use a default address (`default_address`) (eg. a physical store) for tax to be calculated from OR prevent the customer from checking out (`prevent_checkout`).

:::note Note!
If you use a default address, make sure you actually provide one or you may end up in an endless loop.
:::

#### No tax rate available

It's possible customers may run into issues where you don't have a Tax Rate setup for their address. For example: if you have a customer who's address is in the North Pole but you only have rates setup in the UK, then no tax rates will be found.

There's two solutions to this problem:

-   Use the 'default rate' (`default_rate`) which will already exist after enabling the Standard Tax Engine.
-   Prevent the customer from checking out (`prevent_checkout`)
