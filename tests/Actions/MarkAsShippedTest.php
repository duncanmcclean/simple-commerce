<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Actions;

use DoubleThreeDigital\SimpleCommerce\Actions\MarkAsShipped;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class MarkAsShippedTest extends TestCase
{
    use SetupCollections;

    public $action;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();

        $this->action = new MarkAsShipped();
    }

    /** @test */
    public function is_visible_to_paid_order()
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
    public function is_not_visible_to_already_shipped_orders()
    {
        $order = Order::create([
            'is_paid'     => true,
            'is_shipped'  => true,
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
    public function is_able_to_be_run_in_bulk_if_all_orders_are_paid()
    {
        $orderOne = Order::create([
            'is_paid'     => true,
        ]);

        $orderTwo = Order::create([
            'is_paid'     => true,
        ]);

        $action = $this->action->visibleToBulk(collect([
            $orderOne->entry(),
            $orderTwo->entry(),
        ]));

        $this->assertTrue($action);
    }

    /** @test */
    public function is_not_able_to_be_run_in_bulk_if_only_some_orders_are_paid()
    {
        $orderOne = Order::create([
            'is_paid'     => true,
        ]);

        $orderTwo = Order::create([
            'is_paid'     => false,
        ]);

        $action = $this->action->visibleToBulk(collect([
            $orderOne->entry(),
            $orderTwo->entry(),
        ]));

        $this->assertFalse($action);
    }

    /** @test */
    public function order_can_be_refunded()
    {
        Collection::make('orders')->save();

        $order = Entry::make()
            ->collection('orders')
            ->id(Stache::generateId())
            ->data([
                'is_paid'      => true,
                'is_shipped'   => false,
            ]);

        $order->save();

        $this->action->run([$order], null);

        $order->fresh();

        $this->assertTrue($order->data()->get('is_shipped'));
        $this->assertNotNull($order->data()->get('shipped_date'));
    }
}
