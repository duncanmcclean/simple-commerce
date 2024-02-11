<?php

use DuncanMcClean\SimpleCommerce\Contracts\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\Calculator;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;

use function PHPUnit\Framework\assertTrue;

uses(SetupCollections::class);

beforeEach(function () {
    $this->useBasicTaxEngine();
});

uses()->group('calculator');

test('does not calculate totals if order is paid', function () {
    $product = Product::make()->price(500);
    $product->save();

    $cart = Order::make()
        ->status(OrderStatus::Placed)
        ->paymentStatus(PaymentStatus::Paid)
        ->lineItems([
            [
                'product' => $product->id,
                'quantity' => 2,
                'total' => 123,
            ],
        ])
        ->grandTotal(123)
        ->itemsTotal(123)
        ->taxTotal(0)
        ->shippingTotal(0)
        ->couponTotal(0);

    $cart->save();

    // This logic has been moved into the Order class, rather than in the Calculator class.
    $calculate = $cart->recalculate();

    assertTrue($calculate instanceof OrderContract);

    expect(123)->toBe($calculate->grandTotal());
    expect(123)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(123)->toBe($calculate->lineItems()->first()->total());
});

test('ensure tax is included when using coupon', function () {
    Config::set('simple-commerce.sites.default.tax.rate', 20);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    $product = Product::make()->price(5000);
    $product->save();

    $coupon = Coupon::make()
        ->code('one-hundred-pence-off')
        ->value(100)
        ->type('percentage')
        ->data([
            'description' => 'One Hundred Pence Off (£1)',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();

    $cart = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 10000,
        ],
    ])->coupon($coupon->id);

    $cart->save();
    $cart->fresh();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(0)->toBe($calculate->grandTotal());
    expect(10000)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(2000)->toBe($calculate->taxTotal());
    expect(12000)->toBe($calculate->couponTotal());

    expect(10000)->toBe($calculate->lineItems()->first()->total());
});
