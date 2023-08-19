<?php

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Calculator\Calculator;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\Orders\Helpers\Postage;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Site;

use function PHPUnit\Framework\assertTrue;

uses(SetupCollections::class);

beforeEach(function () {
    $this->useBasicTaxEngine();
});

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

test('standard product price is calculated correctly', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);

    $product = Product::make()->price(500);
    $product->save();

    $cart = Order::make()->paymentStatus(PaymentStatus::Paid)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 1,
            'total' => 500,
        ],
    ])->grandTotal(500)->itemsTotal(500)->taxTotal(0)->shippingTotal(0)->couponTotal(0);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(500)->toBe($calculate->grandTotal());
    expect(500)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(500)->toBe($calculate->lineItems()->first()->total());
});

test('variant product price is calculated correctly', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);

    $product = Product::make()
        ->productVariants([
            'options' => [
                [
                    'key' => 'Red_Large',
                    'variant' => 'Red, Large',
                    'price' => 250,
                ],
            ],
        ]);

    $product->save();

    $cart = Order::make()->paymentStatus(PaymentStatus::Paid)->lineItems([
        [
            'product' => $product->id,
            'variant' => 'Red_Large',
            'quantity' => 1,
            'total' => 250,
        ],
    ])->grandTotal(250)->itemsTotal(250)->taxTotal(0)->shippingTotal(0)->couponTotal(0);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(250)->toBe($calculate->grandTotal());
    expect(250)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(250)->toBe($calculate->lineItems()->first()->total());
});

test('ensure decimals in standard product prices are stripped out', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);

    $product = Product::make()->price(15.50);
    $product->save();

    $cart = Order::make()->paymentStatus(PaymentStatus::Paid)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1550,
        ],
    ])->grandTotal(1550)->itemsTotal(1550)->taxTotal(0)->shippingTotal(0)->couponTotal(0);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(1550)->toBe($calculate->grandTotal());
    expect(1550)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(1550)->toBe($calculate->lineItems()->first()->total());
});

test('ensure decimals in variant product prices are stripped out', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);

    $product = Product::make()
        ->productVariants([
            'options' => [
                [
                    'key' => 'Red_Large',
                    'variant' => 'Red, Large',
                    'price' => 15.50,
                ],
            ],
        ]);

    $product->save();

    $cart = Order::make()->paymentStatus(PaymentStatus::Paid)->lineItems([
        [
            'product' => $product->id,
            'variant' => 'Red_Large',
            'quantity' => 1,
            'total' => 1550,
        ],
    ])->grandTotal(1550)->itemsTotal(1550)->taxTotal(0)->shippingTotal(0)->couponTotal(0);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(1550)->toBe($calculate->grandTotal());
    expect(1550)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(1550)->toBe($calculate->lineItems()->first()->total());
});

test('can calculate correct tax amount', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);

    $product = Product::make()->price(1000);
    $product->save();

    $cart = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ]);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(2400)->toBe($calculate->grandTotal());
    expect(2000)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(400)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(2000)->toBe($calculate->lineItems()->first()->total());
});

test('ensure shipping price is applied correctly', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);

    SimpleCommerce::registerShippingMethod(Site::current()->handle(), Postage::class);

    $product = Product::make()->price(1000);
    $product->save();

    $cart = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ])->merge([
        'shipping_method' => Postage::class,
    ]);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(2650)->toBe($calculate->grandTotal());
    expect(2000)->toBe($calculate->itemsTotal());
    expect(250)->toBe($calculate->shippingTotal());
    expect(400)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(2000)->toBe($calculate->lineItems()->first()->total());
});

test('ensure grand total is calculated correctly', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);

    SimpleCommerce::registerShippingMethod(Site::current()->handle(), Postage::class);

    $product = Product::make()->price(1000);
    $product->save();

    $coupon = Coupon::make()
        ->code('half-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Half Price',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();

    $cart = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ])->coupon($coupon->id)->merge([
        'shipping_method' => Postage::class,
    ]);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(1450)->toBe($calculate->grandTotal());
    expect(2000)->toBe($calculate->itemsTotal());
    expect(250)->toBe($calculate->shippingTotal());
    expect(400)->toBe($calculate->taxTotal());
    expect(1200)->toBe($calculate->couponTotal());

    expect(2000)->toBe($calculate->lineItems()->first()->total());
});

test('ensure percentage coupon is calculated correctly on items total', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    $product = Product::make()->price(5000);
    $product->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
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

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(5000)->toBe($calculate->grandTotal());
    expect(10000)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(5000)->toBe($calculate->couponTotal());

    expect(10000)->toBe($calculate->lineItems()->first()->total());
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/651
 */
test('ensure percentage coupon is calculated correctly on items total when value is a decimal number', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    $product = Product::make()->price(5000);
    $product->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value('10.00')
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
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

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(9000)->toBe($calculate->grandTotal());
    expect(10000)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(1000)->toBe($calculate->couponTotal());

    expect(10000)->toBe($calculate->lineItems()->first()->total());
});

/**
 * Ref mentioned screencast on: https://github.com/duncanmcclean/simple-commerce/issues/651
 */
test('ensure percentage coupon is calculated correctly on items total when product price has pence', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    $product = Product::make()->price(2499);
    $product->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value('10')
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();

    $cart = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 1,
            'total' => 2499,
        ],
    ])->coupon($coupon->id);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(2249)->toBe($calculate->grandTotal());
    expect(2499)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(250)->toBe($calculate->couponTotal());

    expect(2499)->toBe($calculate->lineItems()->first()->total());
});

test('ensure fixed coupon is calculated correctly on items total', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    $product = Product::make()->price(5000);
    $product->save();

    $coupon = Coupon::make()
        ->code('one-hundred-pence-off')
        ->value(100)
        ->type('fixed')
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

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(9900)->toBe($calculate->grandTotal());
    expect(10000)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(100)->toBe($calculate->couponTotal());

    expect(10000)->toBe($calculate->lineItems()->first()->total());
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/651
 */
test('ensure fixed coupon is calculated correctly on items total when value is a decimal number', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    $product = Product::make()->price(5000);
    $product->save();

    $coupon = Coupon::make()
        ->code('one-hundred-pence-off')
        ->value('10.00')
        ->type('fixed')
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

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(9000)->toBe($calculate->grandTotal());
    expect(10000)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(0)->toBe($calculate->taxTotal());
    expect(1000)->toBe($calculate->couponTotal());

    expect(10000)->toBe($calculate->lineItems()->first()->total());

    expect(1000)->toBe($coupon->value());
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

test('ensure product price hook is used to determine price of product', function () {
    $product = Product::make()->price(100);
    $product->save();

    SimpleCommerce::productPriceHook(function ($order, $product) {
        return $product->price() * 2;
    });

    $cart = Order::make()
        ->status(OrderStatus::Cart)
        ->lineItems([
            [
                'product' => $product->id,
                'quantity' => 1,
                'total' => 0,
            ],
        ])
        ->grandTotal(0)
        ->itemsTotal(0);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(240)->toBe($calculate->grandTotal());
    expect(200)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(40)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(200)->toBe($calculate->lineItems()->first()->total());

    // Revert hook
    SimpleCommerce::productPriceHook(function ($order, $product) {
        return $product->price();
    });
});

test('ensure product variant price hook is used to determine price of product variant', function () {
    $product = Product::make()
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colours',
                    'values' => [
                        'Red',
                    ],
                ],
                [
                    'name' => 'Sizes',
                    'values' => [
                        'Small',
                    ],
                ],
            ],
            'options' => [
                [
                    'key' => 'Red_Small',
                    'variant' => 'Red Small',
                    'price' => 100,
                ],
            ],
        ]);

    $product->save();

    SimpleCommerce::productVariantPriceHook(function ($order, $product, $variant) {
        return $variant->price() * 2;
    });

    $cart = Order::make()
        ->status(OrderStatus::Cart)
        ->lineItems([
            [
                'product' => $product->id,
                'variant' => 'Red_Small',
                'quantity' => 1,
                'total' => 0,
            ],
        ])
        ->grandTotal(0)
        ->itemsTotal(0);

    $cart->save();

    $calculate = Calculator::calculate($cart);

    assertTrue($calculate instanceof OrderContract);

    expect(240)->toBe($calculate->grandTotal());
    expect(200)->toBe($calculate->itemsTotal());
    expect(0)->toBe($calculate->shippingTotal());
    expect(40)->toBe($calculate->taxTotal());
    expect(0)->toBe($calculate->couponTotal());

    expect(200)->toBe($calculate->lineItems()->first()->total());

    // Revert hook
    SimpleCommerce::productVariantPriceHook(function ($order, $product, $variant) {
        return $variant->price();
    });
});
