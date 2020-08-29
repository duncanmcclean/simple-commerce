<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\User;

class CartControllerTest extends TestCase
{
    use CollectionSetup;

    /** @test */
    public function can_get_cart_index()
    {
        Config::set('statamic.system.track_last_update', false);

        $id = (string) Stache::generateId();
        $this->setupCollections();

        $user = User::make()
            ->email('chew@bacca.com')
            ->data(['name' => 'Chewbacca'])
            ->makeSuper();

        $entry = Entry::make()
            ->collection('orders')
            ->id($id)
            ->slug($id)
            ->data([
                // 'title' => '#'.SimpleCommerce::freshOrderNumber(),
                'title'          => 'Test',
                'items'          => [],
                'is_paid'        => false,
                'grand_total'    => 0,
                'items_total'    => 0,
                'tax_total'      => 0,
                'shipping_total' => 0,
                'coupon_total'   => 0,
            ])
            ->save();

        dd($entry);

        // $cart = Cart::make()->save();

        // Session::shouldReceive('get')
        //     ->once()
        //     ->with('simple-commerce-cart')
        //     ->andReturn($cart->id);

        // $response = $this->get(route('statamic.simple-commerce.cart.index'));

        // dd($response->json());
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
