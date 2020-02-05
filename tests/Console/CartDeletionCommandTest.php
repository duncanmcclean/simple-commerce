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
    use RefreshDatabase;

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
            'uuid' => $cart->uuid
        ]);

        $delete = $this->cartDeletion->deletion();

        $this->assertDatabaseHas('carts', [
            'uuid' => $cart->uuid
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
                'uuid' => $cart->uuid,
            ])
            ->assertDatabaseHas('cart_items', [
                'uuid' => $item->uuid,
            ])
            ->assertDatabaseHas('cart_shipping', [
                'uuid' => $shipping->uuid,
            ]);

        $delete = $this->cartDeletion->deletion();

        $this
            ->assertDatabaseMissing('carts', [
                'uuid' => $cart->uuid,
            ])
            ->assertDatabaseMissing('cart_items', [
                'uuid' => $item->uuid,
            ])
            ->assertDatabaseMissing('cart_shipping', [
                'uuid' => $shipping->uuid,
            ]);
    }
}
