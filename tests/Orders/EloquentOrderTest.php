<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderModel;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use DoubleThreeDigital\SimpleCommerce\Tests\UseDatabaseContentDrivers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class EloquentOrderTest extends TestCase
{
    use RefreshDatabase, UseDatabaseContentDrivers;

    /** @test */
    public function can_get_all_orders()
    {
        OrderModel::create([
            'items' => [
                [
                    'product' => 'blah',
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
            'data' => [
                'foo' => 'bar',
            ],
        ]);

        OrderModel::create([
            'items' => [
                [
                    'product' => 'rarh',
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
            'data' => [
                'boo' => 'foo',
            ],
        ]);

        $all = Order::all();

        $this->assertTrue($all instanceof Collection);
        $this->assertSame($all->count(), 2);
    }

    /** @test */
    public function can_find_order()
    {
        $order = OrderModel::create([
            'items' => [
                [
                    'product' => 'blah',
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
            'data' => [
                'foo' => 'bar',
            ],
        ]);

        $find = Order::find($order->id);

        $this->assertSame($find->id(), $order->id);
        $this->assertSame($find->lineItems()->count(), 1);
        $this->assertSame($find->get('foo'), 'bar');
    }

    /** @test */
    public function can_create()
    {
        $create = Order::make()
            ->isPaid(true)
            ->grandTotal(1000);

        $create->save();

        $this->assertNotNull($create->id());
        $this->assertSame($create->isPaid(), true);
        $this->assertSame($create->grandTotal(), 1000);
    }

    /** @test */
    public function can_save()
    {
        $orderRecord = OrderModel::create([
            'items' => [
                [
                    'product' => 'blah',
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
            'data' => [
                'foo' => 'bar',
            ],
        ]);

        $order = Order::find($orderRecord->id);

        $order->set('is_special_order', true);

        $order->save();

        $this->assertSame($order->id(), $orderRecord->id);
        $this->assertSame($order->get('is_special_order'), true);
    }

    /** @test */
    public function can_delete()
    {
        $orderRecord = OrderModel::create([
            'is_paid' => true,
            'grand_total' => 1000,
        ]);

        $order = Order::find($orderRecord->id);

        $order->delete();

        $this->assertDatabaseMissing('orders', [
            'id' => $orderRecord->id,
            'is_paid' => true,
            'grand_total' => 1000,
        ]);
    }
}
