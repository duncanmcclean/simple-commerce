<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Tags\CartTags;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTagsTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    public $tag;

    public function setUp() : void
    {
        parent::setUp();

//        $this->tag = new CartTags();
    }

    /** @test */
    public function cart_tag_is_registered()
    {
        $this->assertTrue(isset(app()['statamic.tags']['cart']));
    }

    /** @test */
    public function cart_index_tag()
    {
        //
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
