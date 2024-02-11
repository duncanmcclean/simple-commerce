<?php

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tags\CheckoutTags;
use DuncanMcClean\SimpleCommerce\Tags\GatewayTags;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways\TestOffsiteGateway;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways\TestOnsiteGateway;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;
use Statamic\Statamic;

beforeEach(function () {
    $this->useBasicTaxEngine();

    $this->tag = resolve(CheckoutTags::class)
        ->setParser(Antlers::parser())
        ->setContext([]);

    $this->gatewaysTag = resolve(GatewayTags::class)
        ->setParser(Antlers::parser())
        ->setContext([]);

    SimpleCommerce::registerGateway(TestOnsiteGateway::class, [
        'is-duncan-cool' => 'yes',
    ]);

    SimpleCommerce::registerGateway(TestOffsiteGateway::class, [
        'is-duncan-cool' => 'no',
    ]);
});

test('can output checkout form', function () {
    fakeCartWithLineItem();

    $this->tag->setParameters([]);

    $this->tag->setContent('
        <h2>Checkout</h2>

        {{ sc:gateways }}
            ---
            {{ name }} - Duncan Cool ({{ config:is-duncan-cool }}) - Haggis - Tatties
            ---
        {{ /sc:gateways }}
    ');

    $usage = $this->tag->index();

    expect($usage)->toContain('Test On-site Gateway - Duncan Cool (yes) - Haggis - Tatties');
    expect($usage)->toContain('<form method="POST" action="http://localhost/!/simple-commerce/checkout"');
});

test('can fetch checkout form data', function () {
    $form = Statamic::tag('sc:checkout')->fetch();

    expect($form['params_html'])->toContain('<input type="hidden" name="_token"');
    expect('method="POST" action="http://localhost/!/simple-commerce/checkout"')->toEqual($form['attrs_html']);

    $this->assertArrayHasKey('_token', $form['params']);
    expect('http://localhost/!/simple-commerce/checkout')->toEqual($form['attrs']['action']);
    expect('POST')->toEqual($form['attrs']['method']);
});

test('gateways tag can get specific gateway', function () {
    fakeCartWithLineItem();

    $this->gatewaysTag->setParameters([]);

    $usage = $this->gatewaysTag->wildcard('testonsitegateway');

    expect($usage['name'])->toContain('Test On-site Gateway');
    expect($usage['handle'])->toContain('testonsitegateway');
});

test('can redirect user to offsite gateway', function () {
    fakeCartWithLineItem();

    $this->tag->setParameters([]);

    $this->expectException(HttpResponseException::class);

    $usage = $this->tag->wildcard('testoffsitegateway');
});

test('can redirect user to offsite gateway with redirect url', function () {
    fakeCartWithLineItem();

    $this->tag->setParameters([
        'redirect' => 'http://localhost/thanks',
    ]);

    $this->expectException(HttpResponseException::class);

    $this->tag->wildcard('testoffsitegateway');
});

test('can redirect user to confirmation page instead of offsite gateway when order total is 0', function () {
    $product = Product::make()->price(1500);
    $product->save();

    $cart = Order::make()->lineItems([
        [
            'product' => $product->id(),
            'quantity' => 1,
            'total' => 1500,
        ],
    ]);

    $cart->save();

    $coupon = Coupon::make()->code('FREEBIE')->value(100)->type('percentage');
    $coupon->save();

    $cart->coupon($coupon);
    $cart->recalculate();
    $cart->save();

    fakeCartWithLineItem($cart);

    Session::shouldReceive('forget');
    Session::shouldReceive('put');

    expect(PaymentStatus::Unpaid)->toBe($cart->fresh()->paymentStatus());

    $this->tag->setParameters([
        'redirect' => 'http://localhost/order-confirmation',
    ]);

    $usage = $this->tag->wildcard('testoffsitegateway');

    expect(PaymentStatus::Paid)->toBe($cart->fresh()->paymentStatus());
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/842
 */
test('cant redirect user to offsite gateway when product in cart does not have enough stock', function () {
    $product = Product::make()->price(1500);
    $product->save();

    $cart = Order::make()->lineItems([
        [
            'product' => $product->id(),
            'quantity' => 1,
            'total' => 1500,
        ],
    ]);

    $cart->recalculate();
    $cart->save();

    fakeCartWithLineItem($cart);

    $this->tag->setParameters([
        'redirect' => 'http://localhost/order-confirmation',
    ]);

    $this->expectException(HttpResponseException::class);

    $usage = $this->tag->wildcard('testoffsitegateway');

    expect(OrderStatus::Cart)->toBe($cart->fresh()->status());
    expect(PaymentStatus::Unpaid)->toBe($cart->fresh()->paymentStatus());
});

// Helpers
function fakeCartWithLineItem($cart = null)
{
    if (is_null($cart)) {
        $product = Product::make()->price(1500);
        $product->save();

        $cart = Order::make()->lineItems([
            [
                'product' => $product->id(),
                'quantity' => 1,
                'total' => 1500,
            ],
        ]);
        $cart->recalculate();
        $cart->save();
    }

    Session::shouldReceive('get')
        ->with('simple-commerce-cart')
        ->andReturn($cart->id);

    Session::shouldReceive('has')
        ->with('simple-commerce-cart')
        ->andReturn(true);

    Session::shouldReceive('token')
        ->andReturn('random-token');

    Session::shouldReceive('has')
        ->with('errors')
        ->andReturn(null);
}
