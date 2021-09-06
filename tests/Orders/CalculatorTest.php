<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Orders\Calculator;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CalculatorTest extends TestCase
{
    use SetupCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function does_not_calculate_totals_if_order_is_paid()
    {
        $product = Product::create([
            'price' => 500,
        ]);

        $cart = Order::create([
            'is_paid'     => true,
            'grand_total' => 123,
            'items_total' => 123,
            'items'       => [
                [
                    'product'  => $product->id,
                    'quantity' => 2,
                    'total'    => 123,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 123);
        $this->assertSame($calculate['items_total'], 123);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 0);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 123);
    }

    /** @test */
    public function standard_product_price_is_calculated_correctly()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 0);

        $product = Product::create([
            'price' => 500,
        ]);

        $cart = Order::create([
            'is_paid'     => true,
            'grand_total' => 500,
            'items_total' => 500,
            'items'       => [
                [
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 500,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 500);
        $this->assertSame($calculate['items_total'], 500);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 0);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 500);
    }

    /** @test */
    public function variant_product_price_is_calculated_correctly()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 0);

        $product = Product::create([
            'product_variants' => [
                'options' => [
                    [
                        'key'     => 'Red_Large',
                        'variant' => 'Red, Large',
                        'price'   => 250,
                    ],
                ],
            ],
        ]);

        $cart = Order::create([
            'is_paid'     => true,
            'grand_total' => 250,
            'items_total' => 250,
            'items'       => [
                [
                    'product'  => $product->id,
                    'variant'  => 'Red_Large',
                    'quantity' => 1,
                    'total'    => 250,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 250);
        $this->assertSame($calculate['items_total'], 250);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 0);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 250);
    }

    /** @test */
    public function ensure_decimals_in_standard_product_prices_are_stripped_out()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 0);

        $product = Product::create([
            'price' => 15.50,
        ]);

        $cart = Order::create([
            'is_paid'     => true,
            'grand_total' => 1550,
            'items_total' => 1550,
            'items'       => [
                [
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1550,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 1550);
        $this->assertSame($calculate['items_total'], 1550);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 0);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 1550);
    }

    /** @test */
    public function ensure_decimals_in_variant_product_prices_are_stripped_out()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 0);

        $product = Product::create([
            'product_variants' => [
                'options' => [
                    [
                        'key'     => 'Red_Large',
                        'variant' => 'Red, Large',
                        'price'   => 15.50,
                    ],
                ],
            ],
        ]);

        $cart = Order::create([
            'is_paid'     => true,
            'grand_total' => 1550,
            'items_total' => 1550,
            'items'       => [
                [
                    'product'  => $product->id,
                    'variant'  => 'Red_Large',
                    'quantity' => 1,
                    'total'    => 1550,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 1550);
        $this->assertSame($calculate['items_total'], 1550);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 0);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 1550);
    }

    /** @test */
    public function can_calculate_tax_correctly_when_included_in_prices()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 20);
        Config::set('simple-commerce.sites.default.tax.included_in_prices', true);

        $product = Product::create([
            'price' => 2000,
        ]);

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 2000,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 2000);
        $this->assertSame($calculate['items_total'], 1667);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 333);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 1667);
    }

    /**
     * @test
     * Inline with the fix suggested here: https://github.com/doublethreedigital/simple-commerce/pull/438#issuecomment-888498198
     */
    public function can_calulate_tax_correctly_when_not_included_in_prices()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 10);
        Config::set('simple-commerce.sites.default.tax.included_in_prices', false);

        $product = Product::create([
            'price' => 2000,
        ]);

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 2000,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 2200);
        $this->assertSame($calculate['items_total'], 2000);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 200);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 2000);
    }

    /** @test */
    public function ensure_tax_is_subracted_from_item_total_if_included_in_price()
    {
        $this->markTestSkipped();

        Config::set('simple-commerce.sites.default.tax.rate', 20);
        Config::set('simple-commerce.sites.default.tax.included_in_prices', true);

        $product = Product::create([
            'price' => 1000,
        ]);

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 2,
                    'total'    => 2000,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 2000);
        $this->assertSame($calculate['items_total'], 1667);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 333);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 1667);
    }

    /** @test */
    public function ensure_tax_is_not_subtracted_from_item_total_if_not_included_in_prices()
    {
        $this->markTestSkipped();

        Config::set('simple-commerce.sites.default.tax.rate', 20);
        Config::set('simple-commerce.sites.default.tax.included_in_prices', false);

        $product = Product::create([
            'price' => 1000,
        ]);

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 2,
                    'total'    => 2000,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 2333);
        $this->assertSame($calculate['items_total'], 2000);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 333);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 2000);
    }

    /** @test */
    public function ensure_round_value_tax_is_calculated_correctly()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 20);
        Config::set('simple-commerce.sites.default.tax.included_in_prices', true);

        $product = Product::create([
            'price' => 2600,
        ]);

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 3,
                    'total'    => 7800,
                ],
            ],
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 7800);
        $this->assertSame($calculate['items_total'], 6500);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 1300);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 6500);
    }

    /** @test */
    public function ensure_shipping_price_is_applied_correctly()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 20);

        Config::set('simple-commerce.sites.default.shipping.methods', [
            Postage::class,
        ]);

        $product = Product::create([
            'price' => 1000,
        ]);

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 2,
                    'total'    => 2000,
                ],
            ],
            'shipping_method' => Postage::class,
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 2650);
        $this->assertSame($calculate['items_total'], 2000);
        $this->assertSame($calculate['shipping_total'], 250);
        $this->assertSame($calculate['tax_total'], 400);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 2000);
    }

    /** @test */
    public function ensure_grand_total_is_calculated_correctly()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 20);

        Config::set('simple-commerce.sites.default.shipping.methods', [
            Postage::class,
        ]);

        $product = Product::create([
            'price' => 1000,
        ]);

        $coupon = Coupon::create([
            'slug'               => 'half-price',
            'title'              => 'Half Price',
            'redeemed'           => 0,
            'value'              => 50,
            'type'               => 'percentage',
            'minimum_cart_value' => null,
        ])->save();

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 2,
                    'total'    => 2000,
                ],
            ],
            'shipping_method' => Postage::class,
            'coupon' => $coupon->id,
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 1450);
        $this->assertSame($calculate['items_total'], 2000);
        $this->assertSame($calculate['shipping_total'], 250);
        $this->assertSame($calculate['tax_total'], 400);
        $this->assertSame($calculate['coupon_total'], 1200);

        $this->assertSame($calculate['items'][0]['total'], 2000);
    }

    /** @test */
    public function ensure_percentage_coupon_is_calculated_correctly_on_items_total()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 0);
        Config::set('simple-commerce.sites.default.shipping.methods', []);

        $product = Product::create([
            'price' => 5000,
        ]);

        $coupon = Coupon::create([
            'slug'               => 'fifty-friday',
            'title'              => 'Fifty Friday',
            'redeemed'           => 0,
            'value'              => 50,
            'type'               => 'percentage',
            'minimum_cart_value' => null,
        ])->save();

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 2,
                    'total'    => 10000,
                ],
            ],
            'coupon' => $coupon->id,
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 5000);
        $this->assertSame($calculate['items_total'], 10000);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 0);
        $this->assertSame($calculate['coupon_total'], 5000);

        $this->assertSame($calculate['items'][0]['total'], 10000);
    }

    /** @test */
    public function ensure_fixed_coupon_is_calculated_correctly_on_items_total()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 0);
        Config::set('simple-commerce.sites.default.shipping.methods', []);

        $product = Product::create([
            'price' => 5000,
        ]);

        $coupon = Coupon::create([
            'slug'               => 'one-hundred-pence-off',
            'title'              => 'One Hundred Pence Off (£1)',
            'redeemed'           => 0,
            'value'              => 100,
            'type'               => 'fixed',
            'minimum_cart_value' => null,
        ])->save();

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 2,
                    'total'    => 10000,
                ],
            ],
            'coupon' => $coupon->id,
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 9900);
        $this->assertSame($calculate['items_total'], 10000);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 0);
        $this->assertSame($calculate['coupon_total'], 100);

        $this->assertSame($calculate['items'][0]['total'], 10000);
    }

    /** @test */
    public function ensure_tax_is_included_when_using_coupon()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 20);
        Config::set('simple-commerce.sites.default.shipping.methods', []);

        $product = Product::create([
            'price' => 5000,
        ]);

        $coupon = Coupon::create([
            'slug'               => 'one-hundred-pence-off',
            'title'              => 'One Hundred Pence Off (£1)',
            'redeemed'           => 0,
            'value'              => 100,
            'type'               => 'percentage',
            'minimum_cart_value' => null,
        ])->save();

        $cart = Order::create([
            'is_paid' => false,
            'items'   => [
                [
                    'product'  => $product->id,
                    'quantity' => 2,
                    'total'    => 10000,
                ],
            ],
            'coupon' => $coupon->id,
        ]);

        $calculate = (new Calculator())->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 0);
        $this->assertSame($calculate['items_total'], 10000);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 2000);
        $this->assertSame($calculate['coupon_total'], 12000);

        $this->assertSame($calculate['items'][0]['total'], 10000);
    }
}

class Postage implements ShippingMethod
{
    public function name(): string
    {
        return __('simple-commerce::shipping.standard_post.name');
    }

    public function description(): string
    {
        return __('simple-commerce::shipping.standard_post.description');
    }

    public function calculateCost(OrderContract $order): int
    {
        return 250;
    }

    public function checkAvailability(Address $address): bool
    {
        return true;
    }
}
