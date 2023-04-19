<?php

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Orders\Calculator;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Shipping\BaseShippingMethod;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Site;

uses(TestCase::class);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 123);
    $this->assertSame($calculate['items_total'], 123);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]->total(), 123);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 500);
    $this->assertSame($calculate['items_total'], 500);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]->total(), 500);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 250);
    $this->assertSame($calculate['items_total'], 250);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]->total(), 250);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 1550);
    $this->assertSame($calculate['items_total'], 1550);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]->total(), 1550);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 1550);
    $this->assertSame($calculate['items_total'], 1550);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]->total(), 1550);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 2400);
    $this->assertSame($calculate['items_total'], 2000);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 400);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]['total'], 2000);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 2650);
    $this->assertSame($calculate['items_total'], 2000);
    $this->assertSame($calculate['shipping_total'], 250);
    $this->assertSame($calculate['tax_total'], 400);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]['total'], 2000);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 1450);
    $this->assertSame($calculate['items_total'], 2000);
    $this->assertSame($calculate['shipping_total'], 250);
    $this->assertSame($calculate['tax_total'], 400);
    $this->assertSame($calculate['coupon_total'], 1200);

    $this->assertSame($calculate['items'][0]['total'], 2000);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 5000);
    $this->assertSame($calculate['items_total'], 10000);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 5000);

    $this->assertSame($calculate['items'][0]['total'], 10000);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 9000);
    $this->assertSame($calculate['items_total'], 10000);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 1000);

    $this->assertSame($calculate['items'][0]['total'], 10000);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 2249);
    $this->assertSame($calculate['items_total'], 2499);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 250);

    $this->assertSame($calculate['items'][0]['total'], 2499);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 9900);
    $this->assertSame($calculate['items_total'], 10000);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 100);

    $this->assertSame($calculate['items'][0]['total'], 10000);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 9000);
    $this->assertSame($calculate['items_total'], 10000);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 0);
    $this->assertSame($calculate['coupon_total'], 1000);

    $this->assertSame($calculate['items'][0]['total'], 10000);

    $this->assertSame($coupon->value(), 1000);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 0);
    $this->assertSame($calculate['items_total'], 10000);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 2000);
    $this->assertSame($calculate['coupon_total'], 12000);

    $this->assertSame($calculate['items'][0]['total'], 10000);
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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 240);
    $this->assertSame($calculate['items_total'], 200);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 40);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]['total'], 200);

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

    $calculate = (new Calculator())->calculate($cart);

    $this->assertIsArray($calculate);

    $this->assertSame($calculate['grand_total'], 240);
    $this->assertSame($calculate['items_total'], 200);
    $this->assertSame($calculate['shipping_total'], 0);
    $this->assertSame($calculate['tax_total'], 40);
    $this->assertSame($calculate['coupon_total'], 0);

    $this->assertSame($calculate['items'][0]['total'], 200);

    // Revert hook
    SimpleCommerce::productVariantPriceHook(function ($order, $product, $variant) {
        return $variant->price();
    });
});

// Helpers
function name(): string
{
    return __('simple-commerce::shipping.standard_post.name');
}

function description(): string
{
    return __('simple-commerce::shipping.standard_post.description');
}

function calculateCost(OrderContract $order): int
{
    return 250;
}

function checkAvailability(OrderContract $order, Address $address): bool
{
    return true;
}
