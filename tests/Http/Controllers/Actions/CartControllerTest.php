<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CartControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        factory(OrderStatus::class)->create(['primary' => true]);
        factory(ShippingRate::class)->create();
    }

    /** @test */
    public function can_store_line_item()
    {
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

        $this
            ->assertDatabaseHas('line_items', [
                'variant_id'    => $variant->id,
                'note'          => 'Pre-order',
            ]);
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
