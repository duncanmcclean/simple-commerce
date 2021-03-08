<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Collection;

class LineItemsTest extends TestCase
{
    use CollectionSetup;

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
    public function can_get_order_item()
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

        $orderItem = $order->lineItem('one-two-three');

        $this->assertIsArray($orderItem);
        $this->assertSame($orderItem, [
            'id'       => 'one-two-three',
            'product'  => 'oon-doo-twa',
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function can_add_order_item()
    {
        $product = Product::create([
            'title' => 'One Two Three',
            'price' => 1000,
        ]);

        $order = Order::create([
            'items' => [],
        ]);

        $orderItem = $order->addlineItem([
            'product'  => $product->id,
            'quantity' => 1,
            'total'    => 0,
        ]);

        $this->assertIsArray($orderItem);
        $this->assertArrayHasKey('id', $orderItem);
        $this->assertSame($orderItem['product'], $product->id);
        $this->assertSame($orderItem['quantity'], 1);
    }

    /** @test */
    public function can_update_order_item()
    {
        $product = Product::create([
            'title' => 'Four Five Six',
            'price' => 1000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => 'ideeeeee-of-item',
                    'product'  => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $orderItem = $order->updatelineItem('ideeeeee-of-item', [
            'quantity' => 5,
        ]);

        $this->assertIsArray($orderItem);
        $this->assertSame($orderItem['id'], 'ideeeeee-of-item');
        $this->assertSame($orderItem['product'], $product->id);
        $this->assertSame($orderItem['quantity'], 5);
    }

    /** @test */
    public function can_remove_order_item()
    {
        $product = Product::create([
            'title' => 'Four Five Six',
            'price' => 1000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => 'ideeeeee-of-item',
                    'product'  => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $lineItems = $order->removelineItem('ideeeeee-of-item');

        $this->assertTrue($lineItems instanceof Collection);
        $this->assertSame($lineItems->count(), 0);
    }

    /** @test */
    public function can_clear_line_items()
    {
        $product = Product::create([
            'title' => 'Four Five Six',
            'price' => 1000,
        ]);

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
