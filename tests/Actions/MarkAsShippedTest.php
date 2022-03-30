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
        $order = Order::make()->isPaid(true);
        $order->save();

        $action = $this->action->visibleTo($order->resource());

        $this->assertTrue($action);
    }

    /** @test */
    public function is_not_visible_to_unpaid_orders()
    {
        $order = Order::make()->isPaid(false);
        $order->save();

        $action = $this->action->visibleTo($order->resource());

        $this->assertFalse($action);
    }

    /** @test */
    public function is_not_visible_to_already_shipped_orders()
    {
        $order = Order::make()->isPaid(true)->isShipped(true);
        $order->save();

        $action = $this->action->visibleTo($order->resource());

        $this->assertFalse($action);
    }

    /** @test */
    public function is_not_visible_to_products()
    {
        $product = Product::make()
            ->price(1200)
            ->data([
                'title' => 'Medium Jumper',
            ]);

        $product->save();

        $action = $this->action->visibleTo($product->resource());

        $this->assertFalse($action);
    }

    /** @test */
    public function is_able_to_be_run_in_bulk_if_all_orders_are_paid()
    {
        $orderOne = Order::make()->isPaid(true);
        $orderOne->save();

        $orderTwo = Order::make()->isPaid(true);
        $orderTwo->save();

        $action = $this->action->visibleToBulk(collect([
            $orderOne->resource(),
            $orderTwo->resource(),
        ]));

        $this->assertTrue($action);
    }

    /** @test */
    public function is_not_able_to_be_run_in_bulk_if_only_some_orders_are_paid()
    {
        $orderOne = Order::make()->isPaid(true);
        $orderOne->save();

        $orderTwo = Order::make()->isPaid(false);
        $orderTwo->save();

        $action = $this->action->visibleToBulk(collect([
            $orderOne->resource(),
            $orderTwo->resource(),
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
