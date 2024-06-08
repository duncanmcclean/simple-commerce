---
title: Events
---

Events can be useful if you need to listen out for when certain things in your application happen.

Simple Commerce provides a couple of events to help make your life easier.

## Listening for events

First, you'll need a Listener class. You can generate one by running `php artisan make:listener NameOfListener`. It'll generate a file in `App\Listeners`.

In the `handle` function of your event listener, that's where you write your logic that you need to happen when a certain event is triggered.

```php
class NameOfListener
{
    public function handle(NameOfEvent $event)
    {
        //
    }
}
```

Before your listener will actually listen to an event, you need to hook it up. You can do this in your `EventServiceProvider`, located in `App\Providers`.

```php
protected $listen = [
	NameOfEvent::class => [
    	NameOfListener::class,
    ],
];
```

And there you go, you have a listener listening to an event!

For more documentation around events and event listeners, consider reading [the Laravel documentation](https://laravel.com/docs/events).

## Available events

### CouponRedeemed

[**`DuncanMcClean\SimpleCommerce\Events\CouponRedeemed`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/CouponRedeemed.php)

This event is fired when a customer adds a coupon to their cart/order.

```php
public function handle(CouponRedeemed $event)
{
	$event->coupon;
	$event->order;
}
```

### OrderPaymentFailed

[**`DuncanMcClean\SimpleCommerce\Events\OrderPaymentFailed`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/OrderPaymentFailed.php)

This event is fired whenever a payment fails to go through.

```php
public function handle(OrderPaymentFailed $event)
{
	$event->order;
}
```

### OrderSaved

[**`DuncanMcClean\SimpleCommerce\Events\OrderSaved`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/OrderSaved.php)

This event is fired when an order has been saved. This event will only be fired when an order is saved via Simple Commerce, not via the Control Panel.

```php
public function handle(OrderSaved $event)
{
	$event->order;
}
```

### OrderStatusUpdated

[**`DuncanMcClean\SimpleCommerce\Events\OrderStatusUpdated`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/OrderStatusUpdated.php)

This event is fired whenever the status of an order changes.

```php
public function handle(OrderStatusUpdated $event)
{
	$event->order;
    $event->orderStatus;
}
```

### PaymentStatusUpdated

[**`DuncanMcClean\SimpleCommerce\Events\PaymentStatusUpdated`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/PaymentStatusUpdated.php)

This event is fired whenever the payment status of an order changes.

```php
public function handle(PaymentStatusUpdated $event)
{
	$event->order;
    $event->paymentStatus;
}
```

### PostCheckout

[**`DuncanMcClean\SimpleCommerce\Events\PostCheckout`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/PostCheckout.php)

This event is fired after the checkout process has been completed.

```php
public function handle(PostCheckout $event)
{
	$event->order;
    $event->request;
}
```

### PreCheckout

[**`DuncanMcClean\SimpleCommerce\Events\PreCheckout`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/PreCheckout.php)

This event is fired before the checkout process begins.

```php
public function handle(PreCheckout $event)
{
	$event->order;
    $event->request;
}
```

### GatewayWebhookReceived

[**`DuncanMcClean\SimpleCommerce\Events\GatewayWebhookReceived`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/GatewayWebhookReceived.php)

This event is fired whenever a webhook request from a gateway is received.

```php
public function handle(GatewayWebhookReceived $event)
{
	$event->payload;
}
```

### StockRunningLow

[**`DuncanMcClean\SimpleCommerce\Events\StockRunningLow`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/StockRunningLow.php)

This event is fired when the [stock](/stock) for a product is running low.

```php
public function handle(StockRunningLow $event)
{
	$event->product;
    $event->stock;
    $event->variant; // If variant holds the stock
}
```

### StockRunOut

[**`DuncanMcClean\SimpleCommerce\Events\StockRunOut`**](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Events/StockRunOut.php)

This event is fired when the [stock](/stock) for a product has ran out.

```php
public function handle(StockRunOut $event)
{
	$event->product;
    $event->stock;
    $event->variant; // If variant holds the stock
}
```
