# Events

Simple Commerce provides quite a few events which can be helpful to listen out for if you want to add functionality to your store.

## List of Events

### `DoubleThreeDigital\SimpleCommerce\Events\AddedToCart`

This event will be triggered whenever an item is added to the customer's cart. It has three parameters: `cart`, `cartItem` and `variant`.

### `DoubleThreeDigital\SimpleCommerce\Events\AttributeUpdated`

This event will be triggered whenever a product or variant attribute is updated. It has one parameter: `attribute`.

### `DoubleThreeDigital\SimpleCommerce\Events\CartCreated`

This event will be triggered whenever a cart is created. It has one parameter: `cart`.

### `DoubleThreeDigital\SimpleCommerce\Events\OrderPaid`

This event will be triggered whenever an order is paid. It has one parameter: `order`.

### `DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded`

This event will be triggered whenever an order is refunded. It has one parameter: `order`.

### `DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated`

This event will be triggered whenever the status of an order has been updated. It has two parameters: `order` and `orderStatus`.

### `DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful`

This event will be triggered whenever the order process has been successful. It has one parameter: `order`.

### `DoubleThreeDigital\SimpleCommerce\Events\ProductCategoryUpdated`

This event will be triggered whenever a product category is updated. It has one parameter: `category`.

### `DoubleThreeDigital\SimpleCommerce\Events\ProductUpdated`

This event will be triggered whenever a product is updated. It has one parameter `product`.

### `DoubleThreeDigital\SimpleCommerce\Events\RemovedFromCart`

This event will be triggered whenever an item is removed from a cart. It has two parameters: `cart` and `variant`.

### `DoubleThreeDigital\SimpleCommerce\Events\ShippingAddedToCart`

This event will be triggered whenever shipping is added to a cart. It has three parameters: `cart`, `cartShipping` and `shippingZone`.

### `DoubleThreeDigital\SimpleCommerce\Events\TaxAddedToCart`

This event will be triggered whenever tax is added to a cart. It has three parameters: `cart`, `cartTax` and `taxRate`.

### `DoubleThreeDigital\SimpleCommerce\Events\VariantLowStock`

This event will be triggered whenever a variant is running low on stock. It has one parameter: `variant`.

### `DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock`

This event will be triggered whenever a variant has run out of stock. It has one parameter: `variant`.

### `DoubleThreeDigital\SimpleCommerce\Events\VariantUpdated`

This event will be triggered whenever a variant has been updated. It has one parameter: `variant`.

## How to listen for events

You can listen for events in your app's `EventServiceProvider` or in your package's `ServiceProvider`. In your `protected $listen` code, you would just do something like this:

```php
<?php

protected $listen = [
    AddedToCart::class => [
        MyListener::class,
    ],
];
```

In this example, you would be listening out for the `AddedToCart` event we provide and you would be invoking the `MyListener` listener, which you'd need to create.
