<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClearCartControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_clear_cart()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $data = [
            'cartId' => $cart->uid,
        ];

        $response = $this->post('/cart/clear', $data);

        $response->assertRedirect();
        $response->assertSessionHas('commerce_cart_id');

        $this->assertDatabaseMissing('simplecommerce_carts', [
            'uid' => $cart->uid,
        ]);
    }
}
