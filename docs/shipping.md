---
title: Shipping
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595078014
id: cca48c13-5085-4c70-be0a-2dd82c7849a5
is_documentation: true
nav_order: 8
---
If you're selling physical products on your store, you'll need a way to ship those products to your customers. Thankfully, Simple Commerce has an easy way to create custom shipping methods for your store.

Every store can have any number of shipping methods. For example, you could use one shipping method for 1st Class mail and others for 2nd and 3rd class mail.

## Creating a shipping method

Simple Commerce doesn't come with any shipping methods out of the box so you'll need to write your own. We do, however have a command you can use to generate the boilerplate for a shipping method.

```
php please make:shipping-method {method name}
```

That command will create a Shipping Method class in your `app\ShippingMethods` folder. It'll look something like this:

```php
<?php

namespace App\ShippingMethods;

use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use Statamic\Entries\Entry;

class FirstClass implements ShippingMethod
{
    public function name(): string
    {
        return 'Name of your shipping method';
    }

    public function description(): string
    {
        return 'Description of your shipping method';
    }

    public function calculateCost(Entry $order): int
    {
        return 0;
    }

    public function checkAvailability(array $address): bool
    {
        return true;
    }
}
```

Here's a quick explanation of what each method does.

* **name:** Should return the name of your shipping method (will be shown to customers)
* **description: ** Should return a description for your shipping method
* **calculateCost:** This method should be where you return the cost of the shipping, based on the order's entry data.
* **checkAvailability:** This method is where an address array is passed in and you should return a boolean of whether or not you ship to that location.

## Configuration

Shipping Methods can be configured on a site-by-site basis, helpful for if you have different version of your store for different countries.

```php
'sites' => [
    'default' => [
        ...

        'shipping' => [
            'methods' => [
                \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class,
            ],
        ],
    ],
],
```

The `methods` array should contain an array of Shipping Method classes, with the `::class` syntax.

## Templating

During the cart/checkout flow, you'll want to do 2 things: first, let the customer enter their shipping address for the order and secondly, let the customer select the shipping method you want to use for the order.

Let's start with the letting the user enter their shipping address. In our starter kit, we have this on the [initial cart page](https://github.com/doublethreedigital/simple-commerce-starter/blob/master/resources/views/cart.antlers.html).

```
{{ sc:cart:update }}
    <input type="text" name="shipping_name" placeholder="Name" value="{{ old:shipping_name }}">
    <input type="text" name="shipping_address" placeholder="Address" value="{{ old:shipping_address }}">
    <input type="text" name="shipping_city" placeholder="City" value="{{ old:shipping_city }}">
    <select name="shipping_country" value="{{ old:shipping_country }}">
        {{ sc:countries }}
            <option value="{{ iso }}">{{ name }}</option>
        {{ /sc:countries }}
    </select>
    <input type="text" name="shipping_zip_code" placeholder="Postal Code" value="{{ old:shipping_zip_code }}">

    <button type="submit">Update Shipping Address</button>
{{ /sc:cart:update }}
```

When submitted, that form will fill in the appropriate address fields.

> **Hot tip:** You can also do `billing_name`, `billing_address`, `billing_city` etc to allow the user to update their billing address.

After the customer has entered their address we can find available shipping methods for them and allow them to select which one they'd like to use. Again, we can use the `{{ sc:cart:update }}` tag to manage this. We also do this on [our starter kit](https://github.com/doublethreedigital/simple-commerce-starter/blob/master/resources/views/cart-shipping.antlers.html).

```
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

After the customer has submitted that form, Simple Commerce will use that shipping method and update the order totals.