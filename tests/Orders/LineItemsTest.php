<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Collection;

class LineItemsTest extends TestCase
{
    use SetupCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
        $this->useBasicTaxEngine();
    }

    /** @test */
    public function can_get_line_items()
    {
        $order = Order::create([
            'items' => [
                [
                    'id'       => 'one-two-three',
                    'product'  => 'oon-doo-twa',
                    'quantity' => 2,
                ],
                [
                    'id'       => 'nine-ten-eleven',
                    'product'  => 'noin-dois-tre',
                    'quantity' => 2,
                ],
            ],
        ]);

        $lineItems = $order->lineItems();

        $this->assertTrue($lineItems instanceof Collection);
        $this->assertSame($lineItems->count(), 2);
    }

    /** @test */
    public function line_items_return_empty_if_order_has_no_items()
    {
        $order = Order::create();

        $lineItems = $order->lineItems();

        $this->assertTrue($lineItems instanceof Collection);
        $this->assertSame($lineItems->count(), 0);
    }

    /** @test */
    public function can_update_line_item()
    {
        $product = Product::make()
            ->data([
                'title' => 'Four Five Six',
                'price' => 1000,
            ]);

        $product->save();

        $order = Order::create([
            'items' => [
                [
                    'id'       => 'ideeeeee-of-item',
                    'product'  => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $update = $order->updateLineItem('ideeeeee-of-item', [
            'quantity' => 3,
            'metadata' => [
                'product_key' => 'gday-mate',
            ],
        ]);

        $this->assertSame($order->lineItems()->count(), 1);

        $this->assertSame($order->lineItems()->first()['quantity'], 3);
        $this->assertArrayHasKey('metadata', $order->lineItems()->first());
    }

    /** @test */
    public function can_clear_line_items()
    {
        $product = Product::make()
            ->data([
                'title' => 'Four Five Six',
                'price' => 1000,
            ]);

        $product->save();

        $order = Order::create([
            'items' => [
                [
                    'id'       => 'ideeeeee-of-item',
                    'product'  => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $lineItems = $order->clearlineItems();

        $this->assertTrue($lineItems instanceof Collection);
        $this->assertSame($lineItems->count(), 0);
    }
}
