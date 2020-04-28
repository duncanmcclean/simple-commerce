<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
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
                '_redirect'  => '/cart',
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
                '_redirect'  => '/cart',
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
        $lineItem = factory(LineItem::class)->create([
            'quantity' => 1,
        ]);

        $this
            ->session(['simple_commerce_cart' => $lineItem->order->uuid])
            ->post(route('statamic.simple-commerce.cart.update'), [
                'line_item' => $lineItem->uuid,
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
    public function can_update_order_addresses()
    {
        $order = factory(Order::class)->create();

        $this
            ->session(['simple_commerce_cart' => $order->uuid])
            ->post(route('statamic.simple-commerce.cart.update'), [
                'shipping_name'                     => 'Ross Geller',
                'shipping_address_1'                => '11 Statamic Way',
                'shipping_address_2'                => '',
                'shipping_address_3'                => '',
                'shipping_city'                     => $this->faker->city,
                'shipping_country'                  => factory(Country::class)->create()->iso,
                'shipping_state'                    => '',
                'shipping_zip_code'                 => $this->faker->postcode,
                'use_shipping_address_for_billing'  => 'on',
                '_redirect'                         => '/checkout',
            ])
            ->assertRedirect('/checkout');

        $address = Address::where('address1', '11 Statamic Way')->first();    

        $this
            ->assertDatabaseHas('orders', [
                'billing_address_id'    => $address->id,
                'shipping_address_id'   => $address->id,
            ]);
    }

    /** @test */
    public function can_clear_order()
    {
        $this->markTestIncomplete();

        // $order = factory(Order::class)->create();

        // $this
        //     ->session(['simple_commerce_cart' => $order->uuid])
        //     ->post(route('statamic.simple-commerce.cart.destroy'), [
        //         'clear'  => true,
        //     ])
        //     ->assertRedirect();

        // $this
        //     ->assertDatabaseMissing('orders', [
        //         'id' => $order->id,
        //     ]);
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
