<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class UpdateOrderStatusControllerTest extends TestCase
{
    /** @test */
    public function can_update_order_status()
    {
        Event::fake();

        $order = factory(Order::class)->create();
        $status = factory(OrderStatus::class)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('orders.status', ['order' => $order->uuid, 'status' => $status->uuid]))
            ->assertRedirect(cp_route('orders.index'))
            ->assertSessionHas('success');

        $this
            ->assertDatabaseHas('orders', [
                'uuid'              => $order->uuid,
                'order_status_id'   => $status->id,
            ]);

        Event::assertDispatched(OrderStatusUpdated::class);
    }
}
