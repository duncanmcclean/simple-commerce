<?php

use Damcclean\Commerce\Helpers\Cart;
use Damcclean\Commerce\Tests\SwappableContent;
use Damcclean\Commerce\Tests\TestCase;
use Statamic\Routing\Router;

class CartTest extends TestCase
{
    /** @test */
    public function can_get_all_items_in_cart()
    {
//        (new SwappableContent())->swapOutContent();
//
//        $request = $this
//            ->withSession([
//                'cart' => [
//                    [
//                        'slug' => 'generic-t-shirt',
//                        'quantity' => 1
//                    ]
//                ]
//            ])
//            ->get('/products');
//
//        $cart = (new Cart())->all();

        // WIP - broken because it relies on routes
    }

    /** @test */
    public function can_add_item_to_cart()
    {
        //
    }

    /** @test */
    public function can_replace_everything_in_cart()
    {
        //
    }

    /** @test */
    public function can_remove_item_from_cart()
    {
        //
    }

    /** @test */
    public function can_get_cart_total()
    {
        //
    }

    /** @test */
    public function can_count_items_in_cart()
    {
        //
    }

    /** @test */
    public function can_clear_everything_in_cart()
    {
        //
    }
}
