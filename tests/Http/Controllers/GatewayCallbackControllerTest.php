<?php

use DuncanMcClean\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways\CallbackTestGateway;

use function Pest\Laravel\get;
use function Pest\Laravel\withoutExceptionHandling;
use function Pest\Laravel\withSession;

it('handles a successful callback response from the gateway', function () {
    CallbackTestGateway::$expectedCallbackResult = true;
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    $order = Order::make()->id('abc')->save();
    $order->save();

    withSession(['simple-commerce-cart' => 'abc'])
        ->get('/!/simple-commerce/gateways/callback_test/callback?_redirect=/order-complete')
        ->assertSessionMissing('simple-commerce-cart')
        ->assertSessionHas('simple-commerce.checkout.success')
        ->assertRedirect('/order-complete');

    expect($order->fresh()->status())->toBe(OrderStatus::Placed);

    CallbackTestGateway::$expectedCallbackResult = null;
});

it('handles an unsuccessful callback response from the gateway', function () {
    CallbackTestGateway::$expectedCallbackResult = false;
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    $order = Order::make()->id('abc')->save();
    $order->save();

    withSession(['simple-commerce-cart' => 'abc'])
        ->get('/!/simple-commerce/gateways/callback_test/callback?_error_redirect=/payment-failed')
        ->assertSessionHas('simple-commerce-cart')
        ->assertSessionMissing('simple-commerce.checkout.success')
        ->assertRedirect('/payment-failed');

    expect($order->fresh()->status())->toBe(OrderStatus::Cart);

    CallbackTestGateway::$expectedCallbackResult = null;
});

it('handles a successful payment status check when gateway does not implement callback method', function () {
    CallbackTestGateway::$expectedCallbackResult = 'throw';
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    $order = Order::make()->id('abc')->paymentStatus(PaymentStatus::Paid)->save();
    $order->save();

    withSession(['simple-commerce-cart' => 'abc'])
        ->get('/!/simple-commerce/gateways/callback_test/callback?_redirect=/order-complete')
        ->assertSessionMissing('simple-commerce-cart')
        ->assertSessionHas('simple-commerce.checkout.success')
        ->assertRedirect('/order-complete');

    expect($order->fresh()->status())->toBe(OrderStatus::Placed);

    CallbackTestGateway::$expectedCallbackResult = null;
});

it('handles an unsuccessful payment status check when gateway does not implement callback method', function () {
    CallbackTestGateway::$expectedCallbackResult = 'throw';
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    $order = Order::make()->id('abc')->paymentStatus(PaymentStatus::Unpaid)->save();
    $order->save();

    withSession(['simple-commerce-cart' => 'abc'])
        ->get('/!/simple-commerce/gateways/callback_test/callback?_error_redirect=/payment-failed')
        ->assertSessionHas('simple-commerce-cart')
        ->assertSessionMissing('simple-commerce.checkout.success')
        ->assertRedirect('/payment-failed')
        ->assertSessionHasErrors();

    expect($order->fresh()->status())->toBe(OrderStatus::Cart);

    CallbackTestGateway::$expectedCallbackResult = null;
});

it('gets the order ID from the request', function () {
    CallbackTestGateway::$expectedCallbackResult = true;
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    $order = Order::make()->id('abc')->save();
    $order->save();

    get('/!/simple-commerce/gateways/callback_test/callback?_order_id=abc&_redirect=/order-complete')
        ->assertSessionMissing('simple-commerce-cart')
        ->assertSessionHas('simple-commerce.checkout.success')
        ->assertRedirect('/order-complete');

    expect($order->fresh()->status())->toBe(OrderStatus::Placed);

    CallbackTestGateway::$expectedCallbackResult = null;
});

it('throws an exception when the provided gateway does not exist', function () {
    $order = Order::make()->id('abc')->save();
    $order->save();

    expect(function () {
        withoutExceptionHandling()
            ->withSession(['simple-commerce-cart' => 'abc'])
            ->get('/!/simple-commerce/gateways/some_gateway_that_does_not_exist/callback');
    })->toThrow(GatewayDoesNotExist::class);

    expect($order->fresh()->status())->toBe(OrderStatus::Cart);
});
