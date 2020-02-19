<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        factory(Currency::class)->create();
    }

    /** @test */
    public function can_get_cart_index_with_no_items()
    {
        $repsonse = $this->get('/cart');

        $repsonse
            ->assertOk()
            ->assertSee('Cart')
            ->assertSee('There are no items in your cart');
    }

    /** @test */
    public function can_get_cart_index_with_items()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uuid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->get('/cart');

        $response
            ->assertOk()
            ->assertSee('Cart')
            ->assertSee($product->title);
    }

    /** @test */
    public function can_add_item_to_cart()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uuid]);

        $response = $this->post('/cart/add', [
            'product' => $product->id,
            'variant' => $variant->id,
            'quantity' => '1',
        ]);

        $response
            ->assertStatus(302);
    }

    /** @test */
    public function can_remove_item_from_cart()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uuid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => '1',
        ]);

        $response = $this->post('/cart/remove', [
            'cartId' => $cart->uuid,
            'item_id' => $cartItem->uuid,
        ]);

        $response
            ->assertStatus(302);
    }
}
