<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class RefundOrderControllerTest extends TestCase
{
    /** @test */
    public function can_refund_an_order()
    {
        Event::fake();

        $order = factory(Order::class)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('orders.refund', ['order' => $order->uuid]))
            ->assertRedirect();

        $this
            ->assertDatabaseHas('orders', [
                'uuid'          => $order->uuid,
                'is_refunded'   => 1,
            ]);

        Event::assertDispatched(OrderRefunded::class);
    }

    /** @test */
    public function cant_refund_an_order_that_has_already_been_refunded()
    {
        Event::fake();

        $order = factory(Order::class)->create([
            'is_refunded' => true,
        ]);

        $this
            ->actAsSuper()
            ->get(cp_route('orders.refund', ['order' => $order->uuid]))
            ->assertRedirect()
            ->assertSessionHas('error');

        Event::assertNotDispatched(OrderRefunded::class);
    }
}
