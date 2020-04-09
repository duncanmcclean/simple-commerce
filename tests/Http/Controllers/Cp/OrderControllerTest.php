<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use App\User;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class OrderControllerTest extends TestCase
{
    /** @test */
    public function can_get_index_of_orders()
    {
        // TODO: control panel testing is still broken :(

//        $user = factory(User::class)->create([
//            'super' => 1,
//        ]);
//        $orders = factory(Order::class, 5)->create();
//
//        $response = $this->actingAs($user)->get(cp_route('orders.index'));
//
//        dd($response);
//
//        $response->assertOk();
//        $response->assertSee('Order #'.$orders[0]['id']);
//        $response->assertSee('Order #'.$orders[1]['id']);
//        $response->assertSee('Order #'.$orders[2]['id']);
//        $response->assertSee('Order #'.$orders[3]['id']);
//        $response->assertSee('Order #'.$orders[4]['id']);
    }

    /** @test */
    public function can_get_index_of_orders_when_there_are_no_orders()
    {
        //
    }
}
