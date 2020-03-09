Simple Commerce provides a set of events which you can listen for in your app or package.

# List of Events

## `AddedToCart`

This event will be triggered whenever an item is added to the customer's cart. It has three parameters: `cart`, `cartItem` and `variant`.

## `AttributeUpdated`

This event will be triggered whenever a product or variant attribute is updated. It has one parameter: `attribute`.

## `CartCreated`

This event will be triggered whenever a cart is created. It has one parameter: `cart`.

## `CustomerCreated`

This event will be triggered whenever a new customer is created. It has one parameter: `customer`.

## `CustomerUpdated`

This event will be triggered whenever a customer is updated. It has one parameter: `customer`.

## `OrderPaid`

This event will be triggered whenever an order is paid. It has one parameter: `order`.

## `OrderRefunded`

This event will be triggered whenever an order is refunded. It has one parameter: `order`.

## `OrderStatusUpdated`

This event will be triggered whenever the status of an order has been updated. It has two parameters: `order` and `orderStatus`.

## `OrderSuccessful`

This event will be triggered whenever the order process has been successful. It has one parameter: `order`.

## `ProductCategoryUpdated`

This event will be triggered whenever a product category is updated. It has one parameter: `category`.

## `ProductUpdated`

This event will be triggered whenever a product is updated. It has one parameter `product`.

## `RemovedFromCart`

This event will be triggered whenever an item is removed from a cart. It has two parameters: `cart` and `variant`.

## `ShippingAddedToCart`

This event will be triggered whenever shipping is added to a cart. It has three parameters: `cart`, `cartShipping` and `shippingZone`.

## `TaxAddedToCart`

This event will be triggered whenever tax is added to a cart. It has three parameters: `cart`, `cartTax` and `taxRate`.

## `VariantLowStock`

This event will be triggered whenever a variant is running low on stock. It has one parameter: `variant`.

## `VariantOutOfStock`

This event will be triggered whenever a variant has run out of stock. It has one parameter: `variant`.

## `VariantUpdated`

This event will be triggered whenever a variant has been updated. It has one parameter: `variant`.

# How to listen for events

You can listen for events in your `EventServiceProvider` or in your package's `ServiceProvider`. In your `protected $listen` code, you would just do something like this:

```php
<?php

protected $listen = [
    AddedToCart::class => [
        MyListener::class,
    ],
];
```

In this example, you would be listening out for the `AddedToCart` event we provide and you would be invoking the `MyListener` listener, which you'd need to create.
