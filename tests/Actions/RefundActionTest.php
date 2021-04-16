<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Actions;

use DoubleThreeDigital\SimpleCommerce\Actions\RefundAction;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class RefundActionTest extends TestCase
{
    public $action;

    public function setUp(): void
    {
        parent::setUp();

        $this->action = new RefundAction();
    }

    /** @test */
    public function is_visible_to_paid_and_non_refunded_order()
    {
        $order = Order::create([
            'is_paid' => true,
        ]);

        $action = $this->action->visibleTo($order->entry());

        $this->assertTrue($action);
    }

    /** @test */
    public function is_not_visible_to_unpaid_orders()
    {
        $order = Order::create([
            'is_paid' => false,
        ]);

        $action = $this->action->visibleTo($order->entry());

        $this->assertFalse($action);
    }

    /** @test */
    public function is_not_visible_to_already_refunded_orders()
    {
        $order = Order::create([
            'is_paid'     => true,
            'is_refunded' => true,
        ]);

        $action = $this->action->visibleTo($order->entry());

        $this->assertFalse($action);
    }

    /** @test */
    public function is_not_visible_to_products()
    {
        $product = Product::create([
            'title' => 'Medium Jumper',
            'price' => 1200,
        ]);

        $action = $this->action->visibleTo($product->entry());

        $this->assertFalse($action);
    }

    /** @test */
    public function is_not_able_to_be_run_in_bulk()
    {
        $order = Order::create([
            'is_paid'     => true,
            'is_refunded' => true,
        ]);

        $action = $this->action->visibleToBulk([$order->entry()]);

        $this->assertFalse($action);
    }

    /** @test */
    public function order_can_be_refunded()
    {
        $this->markTestSkipped();

        $collection = Collection::make('orders')->save();

        $order = Entry::make()
            ->collection('orders')
            ->id(Stache::generateId())
            ->data([
                'is_paid'      => true,
                'is_refunded'  => false,
                'gateway'      => 'DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway',
                'gateway_data' => [
                    'id' => '123456789abcdefg',
                ],
            ])
            ->save();

        $action = $this->action->run([$order], null);

        $order->fresh();

        $this->assertTrue($order->data()->get('is_refunded'));
        $this->assertArrayHasKey('refund', $order->data()->get('gateway_data'));
    }
}
