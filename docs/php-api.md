---
title: PHP API
---

This documentation will give you a brief overview of how you can interact with Simple Commerce in PHP & what methods are available and most useful to you. If you can't find what you're looking for in here, you might want to source-dive the Simple Commerce codebase or [ask for help on GitHub](https://github.com/duncanmcclean/simple-commerce/discussions/new/choose).

If you've ever used Statamic's `Entry` facade, a lot of the syntax and methodologies are the same in Simple Commerce.

## Products

You can use the `Product` facade to get products out of Simple Commerce.

```php
use DuncanMcClean\SimpleCommerce\Facades\Product;

$product = Product::find('id-of-product');
```

The facade will return an instance of the `Product` class which represents products internally in Simple Commerce. It includes methods for things like price, product variants, etc.

```php
// Get the price (returns an integer)
$product->price();

// Set the price
$product->price(2599);

// Get the product's variant options (returns an Illuminate Collection)
$product->variantOptions();

// Get a specific variant (returns a ProductVariant instance)
$product->variant('Red_Large');

// Set product variants & options.
$product->productVariants([
    'variants' => [
        ['name' => 'Colours', 'values' => ['Red', 'Green', 'Blue']],
        ['name' => 'Size', 'value' => ['Small', 'Medium']],
    ],
    'options' => [
        ['key' => 'Red_Small', 'variant' => 'Red Small', 'price' => 2500],
        ['key' => 'Red_Medium', 'variant' => 'Red Medium', 'price' => 2700],
        ['key' => 'Green_Small', 'variant' => 'Green Small', 'price' => 2500],
        // ...
    ],
]);

// Set the product title (works the same for any Entry data)
$product->set('title', 'Tartan Kilt');
```

You can use `$product->resource()` to get the product entry.

If you make any changes to the product, you can call the `$product->save()` method to save those changes. `$product->delete()` is also there for deleting products.

To make your own product, you can use the following syntax:

```php
$product = Product::make()
    ->data([
        'title' => 'Sporran',
    ])
    ->price(1599);

$product->save();
```

## Orders

You can use the `Order` facade to get orders out of Simple Commerce.

```php
use DuncanMcClean\SimpleCommerce\Facades\Order;

$order = Order::find('id-of-order');
```

The facade will return an instance of the `Order` class which represents orders internally in Simple Commerce. It includes methods for things like order numbers, statuses, totals, etc.

```php
// Get the order number
$order->orderNumber();

// Get the order status (returns the OrderStatus enum)
$order->status();

// Set the order status
$order->updateOrderStatus(OrderStatus::Placed);

// Get the payment status (returns the PaymentStatus enum)
$order->paymentStatus();

// Set the payment status
$order->updatePaymentStatus(PaymentStatus::Paid);

// Get the grand total (returns an integer)
$order->grandTotal();

// Get the items total (returns an integer)
$order->itemsTotal();

// Get the related customer (returns a Customer instance)
$order->customer();

// Set the customer (either pass in the ID of a customer or a Customer instance)
$order->customer($customer);

// Get the used coupon (returns a Coupon instance)
$order->coupon();

// Set the coupon (either pass in the ID of a coupon or a Coupon instance)
$order->coupon($coupon);

// Get the order's gateway & payment data
$order->gatewayData()->gateway()->name(); // Returns the gateway's name
$order->gatewayData()->data()->all(); // Returns an array of the gateway/payment data

// Get the shipping address (returns an Address instance)
$shippingAddress = $order->shippingAddress();

$shippingAddress->fullName();
$shippingAddress->firstName();
$shippingAddress->lastName();
$shippingAddress->addressLine1();
$shippingAddress->addressLine2();
$shippingAddress->city();
$shippingAddress->region();
$shippingAddress->country();
$shippingAddress->zipCode();
$shippingAddress->toArray();
(string) $shippingAddress;

// Redeem a coupon
$order->redeemCoupon('coupon-code');

// Get the status log
$order->statusLog();

// Recalculate order totals
$order->recalculate();

// Get an order's line items (returns an Illuminate Collection)
$lineItems = $order->lineItems();

$lineItems->first()->id();
$lineItems->first()->product();
$lineItems->first()->variant();
$lineItems->first()->quantity();
$lineItems->first()->total();
$lineItems->first()->tax();
$lineItems->first()->metadata();
$lineItems->first()->toArray();

// Add a line item
$order->addLineItem([
    'product' => 'product-id',
    'quantity' => 1,
    'total' => 1500,
]);

// Update a line item
$order->updateLineItem($lineItemId, [
    'quantity' => 2,
]);

// Remove a line item
$order->removeLineItem($lineItemId);

// Clear all line items from the order
$order->clearLineItems();
```

You can use `$order->resource()` to get the Entry or the Eloquent model of the order.

If you make any changes to the order, you can call the `$order->save()` method to save those changes. `$order->delete()` is also there for deleting orders.

To make your own order, you can use the following syntax:

```php
$order = Order::make()
    ->addLineItem([
        'product' => 'product-id',
        'quantity' => 1,
        'total' => 1500,
    ]);

$order->save();
```

When you `->save()`, the order's totals will be automatically recalculated. You may bypass the recalculation by wrapping the operations inside the `withoutRecalculating` method:

```php
$order->withoutRecalculating(function () use (&$order) {
    return $cart->addLineItem([
        'product' => 'another-product-id',
        'quantity' => 1,
        'total' => 1000,
    ]);
});
```

## Customers

You can use the `Customer` facade to get customers out of Simple Commerce.

```php
use DuncanMcClean\SimpleCommerce\Facades\Customer;

$customer = Customer::find('id-of-customer');
```

You can also use the `findByEmail` method on the `Customer` facade to find a customer by their email address:

```php
use DuncanMcClean\SimpleCommerce\Facades\Customer;

$customer = Customer::findByEmail('email@example.com');
```

The facade will return an instance of the `Customer` class which represents customers internally in Simple Commerce. It includes methods for things like price, customers variants, etc.

```php
// Get the customer's name
$customer->name();

// Get the customer's email
$customer->email();

// Get the customer's orders (returns an Illuminate Collection)
$customer->orders();

// Set additional data on the customer
$customer->set('is_vip_customer', true);
```

You can use `$customer->resource()` to get the customer entry/user/Eloquent model.

If you make any changes to the customer, you can call the `$customer->save()` method to save those changes. `$customer->delete()` is also there for deleting customers.

To make your own customer, you can use the following syntax:

```php
$customer = Customer::make()
    ->set('name', 'Joe Smith')
    ->email('joe.smith@example.com');

$customer->save();
```
