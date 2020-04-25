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
            ->session(['simple_commerce_cart' => $order->uuid])
            ->post(route('statamic.simple-commerce.cart.store'), [
                'variant'   => $variant->uuid,
                'quantity'  => 1,
                'note'      => 'Pre-order',
                'redirect'  => '/cart',
            ])
            ->assertRedirect('/cart');

        $this
            ->assertDatabaseHas('line_items', [
                'variant_id'    => $variant->id,
                'note'          => 'Pre-order',
            ]);
    }

    /** @test */
    public function can_store_line_item_without_note()
    {
        $order = factory(Order::class)->create();
        $variant = factory(Variant::class)->create();

        $this
            ->session(['simple_commerce_cart' => $order->uuid])
            ->post(route('statamic.simple-commerce.cart.store'), [
                'variant'   => $variant->uuid,
                'quantity'  => 1,
                'redirect'  => '/cart',
            ])
            ->assertRedirect('/cart');

        $this
            ->assertDatabaseHas('line_items', [
                'variant_id'    => $variant->id,
            ]);
    }

    /** @test */
    public function can_update_line_item_quantity()
    {
        $lineItem = factory(LineItem::class)->create();

        $this
            ->session(['simple_commerce_cart' => $lineItem->order->uuid])
            ->post(route('statamic.simple-commerce.cart.update'), [
                'quantity'  => 2,
            ])
            ->assertRedirect();

        $this
            ->assertDatabaseHas('line_items', [
                'id'         => $lineItem->id,
                'quantity'   => 2,
            ]);
    }

    /** @test */
    public function can_clear_order()
    {
        $order = factory(Order::class)->create();

        $this
            ->session(['simple_commerce_cart' => $order->uuid])
            ->post(route('statamic.simple-commerce.cart.destroy'), [
                'clear'  => true,
            ])
            ->assertRedirect();

        $this
            ->assertDatabaseMissing('orders', [
                'id' => $order->id,
            ]);
    }

    /** @test */
    public function can_remove_line_item_from_order()
    {
        $order = factory(Order::class)->create();
        $lineItem = factory(LineItem::class, 2)->create();

        $this
            ->session(['simple_commerce_cart' => $order->uuid])
            ->post(route('statamic.simple-commerce.cart.destroy'), [
                'line_item'  => $lineItem[0]['uuid'],
            ])
            ->assertRedirect();

        $this
            ->assertDatabaseMissing('line_items', [
                'uuid' => $lineItem[0]['uuid'],
            ])
            ->assertDatabaseHas('line_items', [
                'uuid' => $lineItem[1]['uuid'],
            ]);
    }
}
