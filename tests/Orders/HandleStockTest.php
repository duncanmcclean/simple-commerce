<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Events\StockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Checkout\HandleStock;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

class HandleStockTest extends TestCase
{
    use SetupCollections;

    /** @test */
    public function can_decrease_stock_for_standard_product()
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

        app(Pipeline::class)
            ->send($order)
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertSame(7, $product->stock());
    }

    /** @test */
    public function cant_decrease_stock_for_standard_product_when_product_has_no_stock()
    {
        $product = Product::make()
            ->price(1200)
            ->stock(0)
            ->data([
                'title' => 'Medium Jumper',
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'quantity' => 1,
                ],
            ]);

        $order->save();

        $this->expectException(CheckoutProductHasNoStockException::class);

        app(Pipeline::class)
            ->send($order)
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertSame(0, $product->stock());
    }

    /** @test */
    public function cant_decrease_stock_for_standard_product_when_quantity_is_greater_than_stock()
    {
        $this->markTestIncomplete("TODO");
    }

    /** @test */
    public function ensure_low_stock_event_is_fired_when_product_stock_is_below_threshold()
    {
        Event::fake();

        Config::set('simple-commerce.low_stock_threshold', 8);

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

        app(Pipeline::class)
            ->send($order)
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertSame(7, $product->stock());

        Event::assertDispatched(StockRunningLow::class);
    }

    /** @test */
    public function can_decrease_stock_for_standard_product_with_non_localised_stock_field()
    {
        $this->markTestIncomplete("TODO");
    }

    /** @test */
    public function can_decrease_stock_for_variant_product()
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

        app(Pipeline::class)
            ->send($order)
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertNull($product->stock());
        $this->assertSame(7, $product->variant('Yellow_Large')->stock());
    }

    /** @test */
    public function cant_decrease_stock_for_variant_product_when_product_has_no_stock()
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
                    'quantity' => 3,
                ],
            ]);

        $order->save();

        $this->expectException(CheckoutProductHasNoStockException::class);

        app(Pipeline::class)
            ->send($order)
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertNull($product->stock());
        $this->assertSame(0, $product->variant('Yellow_Large')->stock());
    }

    /** @test */
    public function cant_decrease_stock_for_variant_product_when_quantity_is_greater_than_stock()
    {
        $this->markTestIncomplete("TODO");
    }

    /** @test */
    public function can_decrease_stock_for_variant_product_with_non_localised_stock_field()
    {
        $this->markTestIncomplete("TODO");
    }

    /** @test */
    public function ensure_low_stock_event_is_fired_when_variant_stock_is_below_threshold()
    {
        Event::fake();

        Config::set('simple-commerce.low_stock_threshold', 8);

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

        app(Pipeline::class)
            ->send($order)
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertNull($product->stock());
        $this->assertSame(7, $product->variant('Yellow_Large')->stock());

        Event::assertDispatched(StockRunningLow::class);
    }
}
