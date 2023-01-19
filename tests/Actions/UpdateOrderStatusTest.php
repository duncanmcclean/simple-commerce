<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Actions;

use DoubleThreeDigital\SimpleCommerce\Actions\UpdateOrderStatus;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class UpdateOrderStatusTest extends TestCase
{
    use SetupCollections;

    public $action;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();

        $this->action = new UpdateOrderStatus();
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
    public function order_can_have_its_status_updated()
    {
        Collection::make('orders')->save();

        $order = Entry::make()
            ->collection('orders')
            ->id(Stache::generateId())
            ->data([
                'order_status' => 'cart',
            ]);

        $order->save();

        $this->action->run([$order], [
            'order_status' => 'dispatched',
        ]);

        $order->fresh();

        $this->assertSame($order->data()->get('order_status'), 'dispatched');
    }
}
