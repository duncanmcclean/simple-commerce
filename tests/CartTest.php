<?php

use Damcclean\Commerce\Helpers\Cart;
use Damcclean\Commerce\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->cart = new Cart();
    }

    /** @test */
    public function a_cart_can_be_created()
    {
        //$cart = $this->cart->create();

        //$this->assertIsString($cart);

        $this->assertTrue(true);
    }

    /** @test */
    public function a_cart_exists()
    {
        //
    }

    /** @test */
    public function can_count_cart_items()
    {
        //
    }

    /** @test */
    public function can_get_cart_items()
    {
        //
    }

    /** @test */
    public function can_add_cart_item_to_cart()
    {
        //
    }

    /** @test */
    public function can_remove_cart_item_from_cart()
    {
        //
    }

    /** @test */
    public function a_cart_can_be_cleared()
    {
        //
    }
}
