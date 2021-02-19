<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Calculator;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CalculatorTest extends TestCase
{
    /** @test */
    public function does_not_calculate_totals_if_order_is_paid()
    {
        $product = Product::create([
            'price' => 500,
        ]);

        $cart = Order::create([
            'is_paid' => true,
            'grand_total' => 123,
            'items_total' => 123,
            'items' => [
                [
                    'product' => $product->id,
                    'quantity' => 2,
                    'total' => 123,
                ],
            ],
        ]);

        $calculate = (new Calculator)->calculate($cart);

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
            'is_paid' => true,
            'grand_total' => 500,
            'items_total' => 500,
            'items' => [
                [
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 500,
                ],
            ],
        ]);

        $calculate = (new Calculator)->calculate($cart);

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
                        'key' => 'Red_Large',
                        'variant' => 'Red, Large',
                        'price' => 250,
                    ]
                ],
            ],
        ]);

        $cart = Order::create([
            'is_paid' => true,
            'grand_total' => 250,
            'items_total' => 250,
            'items' => [
                [
                    'product' => $product->id,
                    'variant' => 'Red_Large',
                    'quantity' => 1,
                    'total' => 250,
                ],
            ],
        ]);

        $calculate = (new Calculator)->calculate($cart);

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
            'is_paid' => true,
            'grand_total' => 1550,
            'items_total' => 1550,
            'items' => [
                [
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1550,
                ],
            ],
        ]);

        $calculate = (new Calculator)->calculate($cart);

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
                        'key' => 'Red_Large',
                        'variant' => 'Red, Large',
                        'price' => 15.50,
                    ]
                ],
            ],
        ]);

        $cart = Order::create([
            'is_paid' => true,
            'grand_total' => 1550,
            'items_total' => 1550,
            'items' => [
                [
                    'product' => $product->id,
                    'variant' => 'Red_Large',
                    'quantity' => 1,
                    'total' => 1550,
                ],
            ],
        ]);

        $calculate = (new Calculator)->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 1550);
        $this->assertSame($calculate['items_total'], 1550);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 0);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 1550);
    }

    /** @test */
    public function can_calculate_correct_tax_amount()
    {
        Config::set('simple-commerce.sites.default.tax.rate', 20);

        $product = Product::create([
            'price' => 1000,
        ]);

        $cart = Order::create([
            'is_paid' => false,
            'items' => [
                [
                    'product' => $product->id,
                    'quantity' => 2,
                    'total' => 2000,
                ],
            ],
        ]);

        $calculate = (new Calculator)->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 2333);
        $this->assertSame($calculate['items_total'], 2000);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 333);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 2000);
    }

    /** @test */
    public function ensure_tax_is_subracted_from_item_total_if_included_in_price()
    {
        //
    }

    /** @test */
    public function ensure_tax_is_not_subtracted_from_item_total_if_not_included_in_prices()
    {
        //
    }

    /** @test */
    public function ensure_shipping_price_is_applied_correctly()
    {
        //
    }

    /** @test */
    public function ensure_grand_total_is_calculated_correctly()
    {
        //
    }

    /** @test */
    public function ensure_percentage_coupon_is_calculated_correctly_on_items_total()
    {
        //
    }

    /** @test */
    public function ensure_fixed_coupon_is_calculated_correctly_on_items_total()
    {
        //
    }
}
