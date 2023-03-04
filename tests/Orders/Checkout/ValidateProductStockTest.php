<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders\Checkout;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Checkout\ValidateProductStock;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Pipeline\Pipeline;

class ValidateProductStockTest extends TestCase
{
    use SetupCollections;

    /** @test */
    public function can_pass_validation_for_standard_product_with_enough_stock()
    {
        $product = Product::make()
            ->price(1200)
            ->stock(10)
            ->data([
                'title' => 'Medium Jumper',
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'quantity' => 3,
                ],
            ]);

        $order->save();

        try {
            app(Pipeline::class)
                ->send($order)
                ->through([ValidateProductStock::class])
                ->thenReturn();

            // No exception was thrown, so we're good.
            $this->assertTrue(true);
        } catch (CheckoutProductHasNoStockException $e) {
            $this->fail('Validation failed when it should have passed.');
        }
    }

    /** @test */
    public function cant_pass_validation_for_standard_product_without_enough_stock_to_fulfill_order()
    {
        $product = Product::make()
            ->price(1200)
            ->stock(5)
            ->data([
                'title' => 'Giant Jumper',
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'quantity' => 20,
                ],
            ]);

        $order->save();

        try {
            app(Pipeline::class)
                ->send($order)
                ->through([ValidateProductStock::class])
                ->thenReturn();

            $this->fail('Validation passed when it should have failed.');
        } catch (CheckoutProductHasNoStockException $e) {
            $this->assertTrue($order->lineItems()->count() === 0);
        }
    }

    /** @test */
    public function cant_pass_validation_for_standard_product_with_no_stock()
    {
        $product = Product::make()
            ->price(1200)
            ->stock(0)
            ->data([
                'title' => 'Tiny Jumper',
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'quantity' => 5,
                ],
            ]);

        $order->save();

        try {
            app(Pipeline::class)
                ->send($order)
                ->through([ValidateProductStock::class])
                ->thenReturn();

            $this->fail('Validation passed when it should have failed.');
        } catch (CheckoutProductHasNoStockException $e) {
            $this->assertTrue($order->lineItems()->count() === 0);
        }
    }

    /** @test */
    public function can_pass_validation_for_variant_product_with_enough_stock()
    {
        $product = Product::make()
            ->productVariants([
                'variants' => [
                    [
                        'name' => 'Colour',
                        'values' => ['Yellow'],
                    ],
                    [
                        'name' => 'Size',
                        'values' => ['Large'],
                    ],
                ],
                'options' => [
                    [
                        'key' => 'Yellow_Large',
                        'variant' => 'Yellow, Large',
                        'price' => 1500,
                        'stock' => 10,
                    ],
                ],
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'variant' => 'Yellow_Large',
                    'quantity' => 3,
                ],
            ]);

        $order->save();

        try {
            app(Pipeline::class)
                ->send($order)
                ->through([ValidateProductStock::class])
                ->thenReturn();

            // No exception was thrown, so we're good.
            $this->assertTrue(true);
        } catch (CheckoutProductHasNoStockException $e) {
            $this->fail('Validation failed when it should have passed.');
        }
    }

    /** @test */
    public function cant_pass_validation_for_variant_product_without_enough_stock_to_fulfill_order()
    {
        $product = Product::make()
            ->productVariants([
                'variants' => [
                    [
                        'name' => 'Colour',
                        'values' => ['Yellow'],
                    ],
                    [
                        'name' => 'Size',
                        'values' => ['Large'],
                    ],
                ],
                'options' => [
                    [
                        'key' => 'Yellow_Large',
                        'variant' => 'Yellow, Large',
                        'price' => 1500,
                        'stock' => 10,
                    ],
                ],
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'variant' => 'Yellow_Large',
                    'quantity' => 25,
                ],
            ]);

        $order->save();

        try {
            app(Pipeline::class)
                ->send($order)
                ->through([ValidateProductStock::class])
                ->thenReturn();

            $this->fail('Validation passed when it should have failed.');
        } catch (CheckoutProductHasNoStockException $e) {
            $this->assertTrue($order->lineItems()->count() === 0);
        }
    }

    /** @test */
    public function cant_pass_validation_for_variant_product_with_no_stock()
    {
        $product = Product::make()
            ->productVariants([
                'variants' => [
                    [
                        'name' => 'Colour',
                        'values' => ['Yellow'],
                    ],
                    [
                        'name' => 'Size',
                        'values' => ['Large'],
                    ],
                ],
                'options' => [
                    [
                        'key' => 'Yellow_Large',
                        'variant' => 'Yellow, Large',
                        'price' => 1500,
                        'stock' => 0,
                    ],
                ],
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'variant' => 'Yellow_Large',
                    'quantity' => 5,
                ],
            ]);

        $order->save();

        try {
            app(Pipeline::class)
                ->send($order)
                ->through([ValidateProductStock::class])
                ->thenReturn();

            $this->fail('Validation passed when it should have failed.');
        } catch (CheckoutProductHasNoStockException $e) {
            $this->assertTrue($order->lineItems()->count() === 0);
        }
    }
}
