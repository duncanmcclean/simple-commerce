<?php

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tags\CheckoutTags;
use DoubleThreeDigital\SimpleCommerce\Tags\GatewayTags;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;
use Statamic\Statamic;

uses(TestCase::class);
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
    fakeCart();

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

    $this->assertStringContainsString('Test On-site Gateway - Duncan Cool (yes) - Haggis - Tatties', $usage);
    $this->assertStringContainsString('<form method="POST" action="http://localhost/!/simple-commerce/checkout"', $usage);
});

test('can fetch checkout form data', function () {
    $form = Statamic::tag('sc:checkout')->fetch();

    $this->assertStringContainsString('<input type="hidden" name="_token"', $form['params_html']);
    $this->assertEquals($form['attrs_html'], 'method="POST" action="http://localhost/!/simple-commerce/checkout"');

    $this->assertArrayHasKey('_token', $form['params']);
    $this->assertEquals($form['attrs']['action'], 'http://localhost/!/simple-commerce/checkout');
    $this->assertEquals($form['attrs']['method'], 'POST');
});

test('gateways tag can get specific gateway', function () {
    fakeCart();

    $this->gatewaysTag->setParameters([]);

    $usage = $this->gatewaysTag->wildcard('testonsitegateway');

    $this->assertStringContainsString('Test On-site Gateway', $usage['name']);
    $this->assertStringContainsString('testonsitegateway', $usage['handle']);
});

test('can redirect user to offsite gateway', function () {
    fakeCart();

    $this->tag->setParameters([]);

    $this->expectException(HttpResponseException::class);

    $usage = $this->tag->wildcard('testoffsitegateway');
});

test('can redirect user to offsite gateway with redirect url', function () {
    fakeCart();

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

    $coupon = Coupon::make()->code('FREEBIE')->value(100)->type('percentage')->enabled(true);
    $coupon->save();

    $cart->coupon($coupon);
    $cart->recalculate();
    $cart->save();

    fakeCart($cart);

    Session::shouldReceive('forget');
    Session::shouldReceive('put');

    $this->assertSame($cart->fresh()->paymentStatus(), PaymentStatus::Unpaid);

    $this->tag->setParameters([
        'redirect' => 'http://localhost/order-confirmation',
    ]);

    $usage = $this->tag->wildcard('testoffsitegateway');

    $this->assertSame($cart->fresh()->paymentStatus(), PaymentStatus::Paid);
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

    fakeCart($cart);

    $this->tag->setParameters([
        'redirect' => 'http://localhost/order-confirmation',
    ]);

    $this->expectException(HttpResponseException::class);

    $usage = $this->tag->wildcard('testoffsitegateway');

    $this->assertSame($cart->fresh()->status(), OrderStatus::Cart);
    $this->assertSame($cart->fresh()->paymentStatus(), PaymentStatus::Unpaid);
});

// Helpers
function fakeCart($cart = null)
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

function name(): string
{
    return 'Test Off-site Gateway';
}

function isOffsiteGateway(): bool
{
    return true;
}

function prepare(Request $request, OrderContract $order): array
{
    return [
        'bagpipes' => 'music',
        'checkout_url' => 'http://backpipes.com',
    ];
}

function checkout(Request $request, OrderContract $order): array
{
    return [];
}

function refund(OrderContract $order): array
{
    return [];
}

function webhook(Request $request)
{
    return 'Success.';
}
