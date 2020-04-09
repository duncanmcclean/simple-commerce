<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class OrderControllerTest extends TestCase
{
    /** @test */
    public function can_get_order_index()
    {
        $orders = factory(Order::class, 5)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('orders.index'))
            ->assertOk()
            ->assertSee('Order #'.$orders[0]['id'])
            ->assertSee('Order #'.$orders[1]['id'])
            ->assertSee('Order #'.$orders[2]['id'])
            ->assertSee('Order #'.$orders[3]['id'])
            ->assertSee('Order #'.$orders[4]['id']);
    }

    /** @test */
    public function can_get_order_index_with_no_orders()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('orders.index'))
            ->assertOk()
            ->assertSee("There's nothing to show");
    }

    /** @test */
    public function can_edit_order()
    {
        $order = factory(Order::class)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('orders.edit', ['order' => $order->uuid]))
            ->assertOk()
            ->assertSee('<publish-form')
            ->assertSee('Order #'.$order->id);
    }

    /** @test */
    public function can_update_order()
    {
        $order = factory(Order::class)->create();

        $this
            ->actAsSuper()
            ->post(cp_route('orders.update', ['order' => $order->uuid]), [
                'customer_id'       => $order->customer_id,
                'billing_address1'  => $order->billingAddress->address1,
                'billing_zip_code'  => $order->billingAddress->zip_code,
                'shipping_address1' => $order->billingAddress->address1,
                'shipping_zip_code' => $order->billingAddress->zip_code,
                'total'             => $order->total,
                'notes'             => 'A nice description',
                'items'             => $order->items,
                'order_status_id'   => $order->order_status_id,
                'currency_id'       => $order->currency_id,
            ])
            ->assertRedirect();
    }

    /** @test */
    public function can_destroy_order()
    {
        $order = factory(Order::class)->create();

        $this
            ->actAsSuper()
            ->delete(cp_route('orders.destroy', ['order' => $order->uuid]))
            ->assertOk();

        $this
            ->assertDatabaseMissing('orders', [
                'uuid' => $order->uuid,
            ]);
    }
}
