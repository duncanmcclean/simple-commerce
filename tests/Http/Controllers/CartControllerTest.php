<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CartControllerTest extends TestCase
{
    use CollectionSetup;

    /** @test */
    public function can_get_cart_index()
    {
        $this->setupCollections();
        $cart = Cart::make()->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->get(route('statamic.simple-commerce.cart.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'title',
                'items',
                'is_paid',
                'grand_total',
                'items_total',
                'tax_total',
                'shipping_total',
                'coupon_total',
            ]);
    }

    /** @test */
    public function can_update_cart()
    {
        //
    }

    /** @test */
    public function can_destroy_cart()
    {
        //
    }
}
