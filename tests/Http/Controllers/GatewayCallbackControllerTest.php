<?php

use DuncanMcClean\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways\CallbackTestGateway;
use Statamic\Facades\Stache;

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

it('does not expose the customer profile in a successful callback response', function () {
    CallbackTestGateway::$expectedCallbackResult = true;
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    $customer = Customer::make()->email('callback-customer@example.com')->data(['name' => 'Private Name'])->save();
    $product = Product::make()->price(1234)->data(['title' => 'Callback Product', 'sku' => 'SKU-123'])->save();
    $order = Order::make()->customer($customer->id())->lineItems([
        ['id' => Stache::generateId(), 'product' => $product->id(), 'quantity' => 1, 'total' => 1234],
    ])->save();

    withSession(['simple-commerce-cart' => $order->id()])
        ->getJson('/!/simple-commerce/gateways/callback_test/callback')
        ->assertOk()
        ->assertJsonPath('cart.id', $order->id())
        ->assertJsonPath('cart.items.0.product.sku', 'SKU-123')
        ->assertJsonPath('cart.items.0.product.raw_price', 1234)
        ->assertJsonMissingPath('cart.customer')
        ->assertJsonMissingPath('cart.customer_id');

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

    withSession(['simple-commerce-cart' => 'abc'])
        ->get('/!/simple-commerce/gateways/callback_test/callback?_order_id=abc&_redirect=/order-complete')
        ->assertSessionMissing('simple-commerce-cart')
        ->assertSessionHas('simple-commerce.checkout.success')
        ->assertRedirect('/order-complete');

    expect($order->fresh()->status())->toBe(OrderStatus::Placed);

    CallbackTestGateway::$expectedCallbackResult = null;
});

it('refuses an order ID from the request that is not the current cart', function (?string $cartId) {
    CallbackTestGateway::$expectedCallbackResult = true;
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    Order::make()->id('attacker')->save();

    $order = Order::make()->id('victim')->status(OrderStatus::Dispatched)->paymentStatus(PaymentStatus::Paid);
    $order->save();

    withSession(array_filter(['simple-commerce-cart' => $cartId]))
        ->getJson('/!/simple-commerce/gateways/callback_test/callback?_order_id=victim&_redirect=/order-complete')
        ->assertForbidden()
        ->assertJsonMissing(['id' => 'victim'])
        ->assertSessionMissing('simple-commerce.checkout.success');

    expect($order->fresh()->status())->toBe(OrderStatus::Dispatched);

    CallbackTestGateway::$expectedCallbackResult = null;
})->with([
    'another cart' => 'attacker',
    'no cart' => null,
]);

it('redirects to a same-host url after a successful callback', function () {
    CallbackTestGateway::$expectedCallbackResult = true;
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    Order::make()->id('abc')->save();

    withSession(['simple-commerce-cart' => 'abc'])
        ->get('/!/simple-commerce/gateways/callback_test/callback?_redirect='.urlencode(url('/order-complete')))
        ->assertRedirect(url('/order-complete'));

    CallbackTestGateway::$expectedCallbackResult = null;
});

it('does not redirect to another host after a callback', function (bool $callbackResult, string $parameter, string $url) {
    CallbackTestGateway::$expectedCallbackResult = $callbackResult;
    SimpleCommerce::registerGateway(CallbackTestGateway::class, []);

    Order::make()->id('abc')->save();

    withSession(['simple-commerce-cart' => 'abc'])
        ->from('/checkout')
        ->get("/!/simple-commerce/gateways/callback_test/callback?{$parameter}=".urlencode($url))
        ->assertRedirect('/checkout');

    CallbackTestGateway::$expectedCallbackResult = null;
})->with([
    'successful callback' => [true, '_redirect', 'https://evil.example'],
    'unsuccessful callback' => [false, '_error_redirect', 'https://evil.example'],
    'protocol-relative url' => [true, '_redirect', '//evil.example'],
]);

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
