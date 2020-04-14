<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CustomerOrderControllerTest extends TestCase
{
    /** @test */
    public function can_get_orders_by_customer()
    {
        $order = factory(Order::class)->create();

        $this
            ->actAsSuper()
            ->post(cp_route('fieldtype-data.customer-orders'), [
                'email' => $order->customer->email,
            ])
            ->assertOk();
    }
}
