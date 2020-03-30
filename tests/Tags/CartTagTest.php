<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTag;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;

class CartTagTest extends TestCase
{
    use RefreshDatabase;

    //public $tag;

    public function setUp() : void
    {
        parent::setUp();

        // TODO: set a session store

//        $this->tag = (new CartTag())
//            ->setParameters(Antlers::parser())
//            ->setContext([]);
    }

    /** @test */
    public function cart_tag_is_registered()
    {
        $this->assertTrue(isset(app()['statamic.tags']['cart']));
    }

    /** @test */
    public function cart_tag_index()
    {
//        $items = factory(CartItem::class, 5)->create([
//            'cart_id' => $this->cart->id,
//        ]);
//
//        $run = $this->tag->index();
//
//        $this->assertIsArray($run);
//        $this->assertStringContainsString($items[2]['title'], json_encode($run));
    }

    /** @test */
    public function cart_tag_items()
    {
        //
    }

    /** @test */
    public function cart_tag_shipping()
    {
        //
    }

    /** @test */
    public function cart_tag_tax()
    {
        //
    }

    /** @test */
    public function cart_tag_count()
    {
        //
    }

    /** @test */
    public function cart_tag_overall_total()
    {
        //
    }

    /** @test */
    public function cart_tag_items_total()
    {
        //
    }

    /** @test */
    public function cart_tag_shipping_total()
    {
        //
    }

    /** @test */
    public function cart_tag_tax_total()
    {
        //
    }
}
