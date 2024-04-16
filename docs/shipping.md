---
title: Shipping
---

When selling physical products, you'll need a way to ship those products to your customers. Sometimes you may want to offer multiple shipping options & the prices may vary on the customer's location.

Simple Commerce includes the concept of **Shipping Methods**. Allowing you to create shipping methods for different shipping options (eg. one for Next Day Delivery, another for Standard delivery or in-store pickup).

## Configuration

Shipping Methods can be configured on a site-by-site basis which is helpful if you have different sites for different countries or regions you serve.

```php
// config/simple-commerce.php

'sites' => [
    'default' => [
        // ...

        'shipping' => [
            'methods' => [
                \DuncanMcClean\SimpleCommerce\Shipping\FreeShipping::class => [],
            ],
        ],
    ],
],
```

The `methods` array should contain an array of Shipping Method classes, with the `::class` syntax. You may also specify a configuration array as the second parameter.

## Third-party Shipping Methods

There's a few third-party shipping methods on the Statamic Marketplace that you can pull into your project:

-   [Australian Post](https://statamic.com/addons/mity-digital/australia-post-shipping-for-simple-commerce)
-   [Sendcloud](https://statamic.com/addons/ray-nl/sendcloud-for-simple-commerce)

## Templating

During the checkout process, you'll want to let the customer enter their shipping address and then select the shipping method they wish to use for the order.

### 1. Prompting the customer for their shipping address

Fields for shipping & billing addresses are included in the default order blueprint.

To let your customer enter their details, simply update those fields using the `{{ sc:cart:update }}` tag.

```antlers
{{ sc:cart:update }}
    <input type="text" name="shipping_name" placeholder="Name" value="{{ old:shipping_name }}">
    <input type="text" name="shipping_address" placeholder="Address" value="{{ old:shipping_address }}">
    <input type="text" name="shipping_city" placeholder="City" value="{{ old:shipping_city }}">
    <input type="text" name="shipping_region" placeholder="Region" value="{{ old:shipping_region }}">
    <select name="shipping_country" value="{{ old:shipping_country }}">
        {{ sc:countries }}
            <option value="{{ iso }}">{{ name }}</option>
        {{ /sc:countries }}
    </select>
    <input type="text" name="shipping_zip_code" placeholder="Postal Code" value="{{ old:shipping_zip_code }}">

    <button type="submit">Update Shipping Address</button>
{{ /sc:cart:update }}
```

If you're using the Starter Kit, customers will be asked to enter their shipping details at the [first step of the checkout process](https://github.com/duncanmcclean/sc-starter-kit/blob/main/resources/views/cart.antlers.html).

:::tip Hot Tip
As mentioned, the default order blueprint also has Billing Address fields. You may do the same thing to allow customers update them - the field names are just `billing_` instead of `shipping_`.
:::

### 2. Allowing the customer to select a shipping method

You should also use the `{{ sc:cart:update }}` tag to allow customers to select the shipping method they wish to use.

You can use the `{{ sc:shipping:methods }}` tag to loop through the available shipping methods for the order.

It'll provide you with variables like name & cost for each of the available shipping methods.

```antlers
{{ sc:cart:update }}
    <p>Please select a shipping method for your order.</p>

    <select name="shipping_method" value="{{ old:shipping_method }}">
        <option value="" disabled selected>Select a Shipping Method</option>
            {{ sc:shipping:methods }}
                <option value="{{ handle }}">{{ name }} - {{ cost }}</option>
            {{ /sc:shipping:methods }}
    </select>

    <button type="submit">Select Shipping Method</button>
{{ /sc:cart:update }}
```

Once the customer has submitted the form, Simple Commerce will update the order totals using the chosen shipping method.

## Default Shipping Method

Normally, Simple Commerce won't calculate the Shipping Total for an order until the customer's entered their shipping address & selected the shipping method they'd like to use.

However, there are some use cases (like for those with only one available shipping method) where you may want to set a default shipping method.

The default shipping method will be used when calculating the Shipping Total for an order if no other Shipping Method has been selected.

```php
'sites' => [
    'default' => [
        ...

        'shipping' => [
            'default_method' => 'free_shipping',

            'methods' => [
                \DuncanMcClean\SimpleCommerce\Shipping\FreeShipping::class => [],
            ],
        ],
    ],
],
```

:::warning Warning
Simple Commerce won't check if the Default Shipping Method is 'available' before using it for orders.
:::


## Building Custom Shipping Methods

To get you up & running quickly, Simple Commerce includes a command to generate the relevant boilerplate for a new shipping method.

```
php please make:shipping-method YourNewShippingMethod
```

That command will create a Shipping Method class in your `app\ShippingMethods` folder. It'll look a little something like this:

```php
<?php

namespace App\ShippingMethods;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Orders\Address;
use DuncanMcClean\SimpleCommerce\Shipping\BaseShippingMethod;

class FirstClass extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return 'Name of your shipping method';
    }

    public function description(): string
    {
        return 'Description of your shipping method';
    }

    public function calculateCost(Order $order): int
    {
        return 0;
    }

    public function checkAvailability(Order $order, Address $address): bool
    {
        return true;
    }
}
```

Here's a quick rundown of what each method does.

-   **name:** Should return the name of your shipping method (will be shown to customers)
-   **description:** Should return a description for your shipping method
-   **calculateCost:** This method should be where you return the cost of the shipping, based on the order's entry data.
-   **checkAvailability:** This method is where an Address object is passed in and you should return a boolean of whether or not you ship to that location.

Inside your shipping method, if you need to accept configuration options (for example: an API Key), you can do so by using the available `config` method.

```php
// app/ShippingMethods/FirstClass.php

$this->config()->get('api_key');
```

Then, inside your config file, setting configuration values looks like this:

```php
// config/simple-commerce.php

'sites' => [
    'default' => [
        // ...

        'shipping' => [
            'methods' => [
                \App\ShippingMethods\FirstClass::class => [
	                'api_key' => 'blahblahblah',
                ],
            ],
        ],
    ],
],
```
