<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTags;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Parse;
use Statamic\Facades\Stache;

class CartTagTest extends TestCase
{
    protected $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = resolve(CartTags::class);
    }

    /** @test */
    public function can_get_index()
    {
        // // TODO: The array stuff here doesn't seem to be working.
        $this->markTestIncomplete();

        $this->fakeSessionCart();

        $this->assertSame('cart', (string) $this->tag('{{ sc:cart }}{{ order_status }}{{ /sc:cart }}'));
        $this->assertSame('false', (string) $this->tag('{{ sc:cart }}{{ is_paid }}{{ /sc:cart }}'));
    }

    /** @test */
    public function user_has_a_cart_if_cart_does_not_exist()
    {
        $this->assertSame('No cart', (string) $this->tag('{{ if sc:cart:has }}Has cart{{ else }}No cart{{ /if }}'));
    }

    /** @test */
    public function user_has_a_cart_if_cart_exists()
    {
        $this->fakeSessionCart();

        $this->assertSame('Has cart', (string) $this->tag('{{ if {sc:cart:has} === true }}Has cart{{ else }}No cart{{ /if }}'));
    }

    /** @test */
    public function can_get_cart_items()
    {
        // TODO: work out issues with toAugmentedArray() playing up in tests
        $this->markTestIncomplete();

        $product = Product::make()
            ->title('Dog Food')
            ->slug('dog-food')
            ->data(['price' => 1000])
            ->save();

        $cart = Cart::make()->save()->update([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 5,
                    'total' => 1000,
                ],
            ],
        ]);

        $this->fakeSessionCart($cart);

        $this->assertStringContainsString('5', $this->tag('{{ sc:cart:items }}{{ quantity }}{{ /sc:cart:items }}'));
    }

    /** @test */
    public function can_get_cart_items_count()
    {
        $productOne = Product::make()
            ->title('Dog Food')
            ->slug('dog-food')
            ->data(['price' => 1000])
            ->save();

        $productTwo = Product::make()
            ->title('Cat Food')
            ->slug('cat-food')
            ->data(['price' => 1200])
            ->save();

        $cart = Cart::make()->save()->update([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $productOne->id,
                    'quantity' => 5,
                    'total' => 1000,
                ],
                [
                    'id' => Stache::generateId(),
                    'product' => $productTwo->id,
                    'quantity' => 5,
                    'total' => 1200,
                ],
            ],
        ]);

        $this->fakeSessionCart($cart);

        $this->assertSame('2', (string) $this->tag('{{ sc:cart:count }}'));
    }

    /** @test */
    public function can_get_cart_total()
    {
        // TODO: work out issues with toAugmentedArray() playing up in tests
        $this->markTestIncomplete();

        $cart = Cart::make()->save()->update([
            'grand_total' => 2550,
        ]);

        $this->fakeSessionCart($cart);

        $this->assertSame('£25.50', $this->tag('{{ sc:cart:total }}'));
    }

    /** @test */
    public function can_get_cart_grand_total()
    {
        //
    }

    /** @test */
    public function can_get_cart_items_total()
    {
        //
    }

    /** @test */
    public function can_get_cart_shipping_total()
    {
        //
    }

    /** @test */
    public function can_get_cart_tax_total()
    {
        //
    }

    /** @test */
    public function can_get_cart_coupon_total()
    {
        //
    }

    protected function tag($tag)
    {
        return Parse::template($tag, []);
    }

    protected function fakeSessionCart($cart = null)
    {
        if (is_null($cart)) {
            $cart = Cart::make()->save();
        }

        Session::shouldReceive('get')
            ->with('simple-commerce-cart')
            ->andReturn($cart->id);

        Session::shouldReceive('has')
            ->with('simple-commerce-cart')
            ->andReturn(true);
    }
}
