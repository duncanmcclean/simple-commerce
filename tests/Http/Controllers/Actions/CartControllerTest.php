<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
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
    public function can_add_item_to_cart()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uuid]);

        $response = $this->post(route('statamic.simple-commerce.cart.store'), [
            'product'   => $product->id,
            'variant'   => $variant->id,
            'quantity'  => '1',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function can_update_item_in_cart()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uuid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id'       => $cart->id,
            'product_id'    => $product->id,
            'variant_id'    => $variant->id,
            'quantity'      => '1',
        ]);

        $response = $this->post(route('statamic.simple-commerce.cart.update'), [
            'item_id' => $cartItem->id,
            'quantity'  => '3',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function can_remove_item_from_cart()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id'    => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uuid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id'       => $cart->id,
            'product_id'    => $product->id,
            'variant_id'    => $variant->id,
            'quantity'      => 1,
        ]);

        $response = $this->post(route('statamic.simple-commerce.cart.destroy'), [
            'item_id'   => $cartItem->uuid,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function can_clear_the_cart()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id'    => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uuid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id'       => $cart->id,
            'product_id'    => $product->id,
            'variant_id'    => $variant->id,
            'quantity'      => 1,
        ]);

        $response = $this->post(route('statamic.simple-commerce.cart.destroy'), [
            'clear'     => 'true',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('commerce_cart_id');

        $this->assertDatabaseMissing('carts', [
            'uuid'  => $cart->uuid,
        ]);
    }
}
