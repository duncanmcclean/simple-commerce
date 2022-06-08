---
title: Shipping
---

If you're selling physical products on your store, you'll need a way to ship those products to your customers. Thankfully, Simple Commerce has an easy way to create custom shipping methods for your store.

Every store can have any number of shipping methods. For example, you could use one shipping method for 1st Class mail and others for 2nd and 3rd class mail.

## Configuration

Shipping Methods can be configured on a site-by-site basis, helpful for if you have different version of your store for different countries.

```php
'sites' => [
    'default' => [
        ...

        'shipping' => [
            'methods' => [
                \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class => [],
            ],
        ],
    ],
],
```

The `methods` array should contain an array of Shipping Method classes, with the `::class` syntax. You may also specify a configuration array as the second parameter.

### Default shipping method

Normally, a shipping total isn't calculated until you've added a Shipping Method to the order. This is usually done after the customer has entered their shipping address and they're then offered an option as to which Shipping Method to use.

However, there are some cases where you may need to calculate Shipping costs before a Shipping Method has been selected on an order. You may also want it for stores where there's only one shipping method available.

In these cases, you may configure a default Shipping Method which will be used when calculating order totals if no Shipping Method has already been set.

```php
'sites' => [
    'default' => [
        ...

        'shipping' => [
            'default_method' => \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class,

            'methods' => [
                \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class => [],
            ],
        ],
    ],
],
```

> Warning: Simple Commerce will not check if the default method is 'available' for the customer before using it.

## Third-party Shipping Methods

* [Australian Post for Simple Commerce](https://statamic.com/addons/mity-digital/australia-post-shipping-for-simple-commerce)

## Templating

During the cart/checkout flow, you'll want to do 2 things: first, let the customer enter their shipping address for the order and secondly, let the customer select the shipping method you want to use for the order.

Let's start with the letting the user enter their shipping address. In our starter kit, we have this on the [initial cart page](https://github.com/doublethreedigital/sc-starter-kit/blob/main/resources/views/cart.antlers.html).

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

When submitted, that form will fill in the appropriate address fields.

> **Hot tip:** You can also do `billing_name`, `billing_address`, `billing_city` etc to allow the user to update their billing address.

After the customer has entered their address we can find available shipping methods for them and allow them to select which one they'd like to use. Again, we can use the `{{ sc:cart:update }}` tag to manage this. We also do this on [our starter kit](https://github.com/doublethreedigital/sc-starter-kit/blob/main/resources/views/checkout/shipping.antlers.html).

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

After the customer has submitted that form, Simple Commerce will use that shipping method and update the order totals.

## Marking an order as shipped

As of Simple Commerce v2.4, you may now mark an order as 'Shipped'. You can either do this programatically or via the Control Panel.

Marking an order as shipped will dispatch an event which you can use to send notifications to customers.

### Programatically

If you want to mark an order as Shipped from your own code, you may use the `markAsShipped` method available on `Order` objects.

```php
use DoubleThreeDigital\SimpleCommerce\Facades\Order;

$order = Order::find(123);
$order->markAsShipped();
```

### Via the Control Panel

> **Note:** this will only show if you're using Collections & Entries for your orders. You'll need to build this yourself for custom [content drivers](/extending/content-drivers).

In the Control Panel listing table for orders, find the order you wish to mark as shipped, click the three dots on the right, and select the 'Mark as Shipped' option.

The action will only be available for order which have already been marked as paid.

![Mark as Shipped](/img/simple-commerce/mark-as-shipped.png)

## Creating a shipping method

Simple Commerce doesn't come with any shipping methods out of the box so you'll need to write your own. We do, however have a command you can use to generate the boilerplate for a shipping method.

```
php please make:shipping-method YourNewShippingMethod
```

That command will create a Shipping Method class in your `app\ShippingMethods` folder. It'll look something like this:

```php
<?php

namespace App\ShippingMethods;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Data\Address;
use DoubleThreeDigital\SimpleCommerce\Shipping\BaseShippingMethod;

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

Here's a quick explanation of what each method does.

- **name:** Should return the name of your shipping method (will be shown to customers)
- **description:** Should return a description for your shipping method
- **calculateCost:** This method should be where you return the cost of the shipping, based on the order's entry data.
- **checkAvailability:** This method is where an Address object is passed in and you should return a boolean of whether or not you ship to that location.

### Using config settings

As mentioned earlier, you may let users of your shipping method specify a configuration array which is accessible inside the Shipping Method itself. If you'd like to do this, you may access the config like so:

```php
// app/ShippingMethods/FirstClass.php

$this->config()->get('api_key');
```
