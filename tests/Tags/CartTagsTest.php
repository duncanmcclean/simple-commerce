<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTags;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Facades\Antlers;

class CartTagsTest extends TestCase
{
    use RefreshDatabase;

    public $tag;

    public function setUp() : void
    {
        parent::setUp();

        $cart = factory(Cart::class)->create();

//        $this->tag = (new CartTags())
//            ->setParser(Antlers::class)
//            ->setContext([]);
//
//        $this->tag->cartId = $cart->uuid;
//        $this->session(['commerce_cart_id' => $cart->uuid]);
    }

    /** @test */
    public function cart_tag_is_registered()
    {
        $this->assertTrue(isset(app()['statamic.tags']['cart']));
    }

    /** @test */
    public function cart_index_tag()
    {
//        $cart = factory(Cart::class)->create();
//        $items = factory(CartItem::class, 5)->create([
//            'cart_id' => $cart->id,
//        ]);
//
//        $usage = $this->tag->index();
//
//        $this->assertIsObject($usage);
    }

    /** @test */
    public function cart_items_tag()
    {
        //
    }

    /** @test */
    public function cart_count_tag()
    {
        //
    }

    /** @test */
    public function cart_total_tag()
    {
        //
    }
}
