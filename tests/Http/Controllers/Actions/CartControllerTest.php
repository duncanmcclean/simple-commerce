<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CartControllerTest extends TestCase
{
    /** @test */
    public function can_store_line_item()
    {
//        Cart::shouldReceive('addLineItem')
//            ->once()
//            ->withAnyArgs()
//            ->andReturnNull();

        $order = factory(Order::class)->create();
        $variant = factory(Variant::class)->create();

        $this
            ->session(['cart_session_key' => $order->uuid])
            ->post(route('statamic.simple-commerce.cart.store'), [
                'variant'   => $variant->uuid,
                'quantity'  => 1,
                'note'      => 'Pre-order',
            ])
            ->assertRedirect();

//        dd('back here');
//        dd(LineItem::all());
//
//        $this
//            ->assertDatabaseHas('line_items', [
//                'order_id'      => $order->id,
//                'variant_id'    => $variant->id,
//                'note'          => 'Pre-order',
//            ]);
    }

    /** @test */
    public function can_store_line_item_without_note()
    {
        //
    }

    /** @test */
    public function can_update_line_item_quantity()
    {
        //
    }

    /** @test */
    public function can_clear_order()
    {

    }

    /** @test */
    public function can_remove_line_item_from_order()
    {
        //
    }
}
