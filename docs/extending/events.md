# Events

Simple Commerce provides quite a few events which can be helpful to listen out for if you want to add functionality to your store.

## List of Events

### `DoubleThreeDigital\SimpleCommerce\Events\AttributeUpdated`

This event will be triggered whenever a product or variant attribute is updated. It has one parameter: `attribute`.

### `DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed`

This event will be triggered during checkout when a line item uses a coupon. It has two parameters: `coupon` and `order`.

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
