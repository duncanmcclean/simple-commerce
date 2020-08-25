<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Content;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\PreventSavingStacheItemsToDisk;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Session;
use Statamic\Auth\File\User;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class CartControllerTest extends TestCase
{
    use CollectionSetup;

    /** @test */
    public function can_get_cart_index()
    {
        $id = (string) Stache::generateId();
        $this->setupCollections();

        $entry = Entry::make()
            ->collection('orders')
            ->id($id)
            ->slug($id)
            ->locale('default') // TODO: wont need this soon
            ->data([
                'title' => '#'.SimpleCommerce::freshOrderNumber(),
                'items'          => [],
                'is_paid'        => false,
                'grand_total'    => 0,
                'items_total'    => 0,
                'tax_total'      => 0,
                'shipping_total' => 0,
                'coupon_total'   => 0,
            ])
            ->save();

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
