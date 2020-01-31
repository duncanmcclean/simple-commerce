<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Console;

use DoubleThreeDigital\SimpleCommerce\Console\Commands\CartDeletionCommand;
use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartDeletionCommandTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    public $cartDeletion;

    public function setUp(): void
    {
        parent::setUp();

        $this->cartDeletion = new CartDeletionCommand();
    }

    /** @test */
    public function fresh_cart_wont_be_deleted()
    {
        $cart = factory(Cart::class)->create();

        $this->assertDatabaseHas('carts', [
            'uid' => $cart->uid
        ]);

        $delete = $this->cartDeletion->deletion();

        $this->assertDatabaseHas('carts', [
            'uid' => $cart->uid
        ]);
    }

    /** @test */
    public function old_cart_will_be_deleted()
    {
        $date = now()->subYear();

        $cart = factory(Cart::class)->create([
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $item = factory(CartItem::class)->create([
            'created_at' => $date,
            'updated_at' => $date,
            'cart_id' => $cart->id,
        ]);

        $shipping = factory(CartShipping::class)->create([
            'created_at' => $date,
            'updated_at' => $date,
            'cart_id' => $cart->id,
        ]);

        $this
            ->assertDatabaseHas('carts', [
                'uid' => $cart->uid,
            ])
            ->assertDatabaseHas('cart_items', [
                'uid' => $item->uid,
            ])
            ->assertDatabaseHas('cart_shipping', [
                'uid' => $shipping->uid,
            ]);

        $delete = $this->cartDeletion->deletion();

        $this
            ->assertDatabaseMissing('carts', [
                'uid' => $cart->uid,
            ])
            ->assertDatabaseMissing('cart_items', [
                'uid' => $item->uid,
            ])
            ->assertDatabaseMissing('cart_shipping', [
                'uid' => $shipping->uid,
            ]);
    }
}
