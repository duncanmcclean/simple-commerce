<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Helpers;

use DoubleThreeDigital\SimpleCommerce\Models\Cart as CartModel;
use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CartTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    public $cart;

    public function setUp(): void
    {
        parent::setUp();

        $this->cart = new Cart();
    }

    /** @test */
    public function a_cart_can_be_created()
    {
        $create = $this->cart->create();

        $this->assertIsString($create);

        $this->assertDatabaseHas('carts', [
            'uid' => $create,
        ]);
    }

    /** @test */
    public function a_cart_exists()
    {
        $cart = factory(CartModel::class)->create();

        $exists = $this->cart->exists($cart->uid);

        $this->assertTrue($exists);
    }

    /** @test */
    public function can_count_cart_items()
    {
        $cart = factory(CartModel::class)->create();
        $items = factory(CartItem::class, 5)->create([
            'cart_id' => $cart->id,
        ]);

        $count = $this->cart->count($cart->uid);

        $this->assertSame($count, 5);
    }

    /** @test */
    public function can_get_cart_items()
    {
        $cart = factory(CartModel::class)->create();
        $items = factory(CartItem::class, 5)->create([
            'cart_id' => $cart->id,
        ]);

        $get = $this->cart->get($cart->uid);

        $this->assertIsObject($get);
    }

    /** @test */
    public function can_add_cart_item_to_cart()
    {
        $cart = factory(CartModel::class)->create();

        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);
        $shipping = factory(ShippingZone::class)->create();
        $tax = factory(TaxRate::class)->create();

        $add = $this->cart->add($cart->uid, [
            'product' => $product->uid,
            'variant' => $variant->uid,
            'quantity' => 1,
        ]);

        $this->assertIsObject($add);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);
    }

    /** @test */
    public function can_remove_cart_item_from_cart()
    {
        $cart = factory(CartModel::class)->create();
        $item = factory(CartItem::class)->create([
            'cart_id' => $cart->id,
        ]);

        $remove = $this->cart->remove($cart->uid, $item->uid);

        $this->assertIsObject($remove);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
            'cart_id' => $cart->id,
        ]);
    }

    /** @test */
    public function a_cart_can_be_cleared()
    {
        $cart = factory(CartModel::class)->create();
        $item = factory(CartItem::class, 5)->create([
            'cart_id' => $cart->id,
        ]);
        $shipping = factory(CartShipping::class)->create([
            'cart_id' => $cart->id,
        ]);
        $shipping = factory(CartTax::class)->create([
            'cart_id' => $cart->id,
        ]);

        $clear = $this->cart->clear($cart->uid);

        $this->assertNull($clear);

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
        ]);

        $this->assertDatabaseMissing('cart_shipping', [
            'cart_id' => $cart->id,
        ]);

        $this->assertDatabaseMissing('cart_taxes', [
            'cart_id' => $cart->id,
        ]);
    }

    /** @test */
    public function a_cart_has_an_overall_total()
    {
        //
    }

    /** @test */
    public function a_cart_has_an_items_total()
    {
        //
    }

    /** @test */
    public function a_cart_has_a_shipping_total()
    {
        //
    }

    /** @test */
    public function a_cart_has_a_tax_total()
    {
        //
    }

    /** @test */
    public function can_get_cart_shipping()
    {
        //
    }

    /** @test */
    public function can_check_if_cart_already_has_shipping()
    {
        //
    }

    /** @test */
    public function can_add_shipping_to_cart()
    {
        //
    }

    /** @test */
    public function can_get_cart_tax()
    {
        //
    }

    /** @test */
    public function can_check_if_cart_already_has_tax()
    {
        //
    }

    /** @test */
    public function can_add_tax_to_cart()
    {
        //
    }
}
