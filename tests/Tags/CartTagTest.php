<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTags;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;
use Statamic\Facades\Parse;
use Statamic\Facades\Stache;

class CartTagTest extends TestCase
{
    protected $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = resolve(CartTags::class)
            ->setParser(Antlers::parser())
            ->setContext([]);
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

    /** @test */
    public function can_output_add_item_form()
    {
        $product = Product::make()
            ->title('Dog Food')
            ->slug('dog-food')
            ->data(['price' => 1000])
            ->save();

        $this->tag->setParameters([]);

        $this->tag->setContent('
            <h2>Add Item</h2>

            <input type="hidden" name="product" value="{{ '.$product->id.' }}">
            <input type="number" name="quantity">
            <button type="submit">Add to cart</submit>
        ');

        $usage = $this->tag->addItem();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart-items"', $usage);
    }

    /** @test */
    public function can_output_update_item_form()
    {
        $this->tag->setParameters([
            'item' => 'absolute-load-of-jiberish'
        ]);

        $this->tag->setContent('
            <h2>Update Item</h2>

            <input type="number" name="quantity">
            <button type="submit">Update item in cart</submit>
        ');

        $usage = $this->tag->updateItem();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart-items/absolute-load-of-jiberish"', $usage);
    }

    /** @test */
    public function can_output_remove_item_form()
    {
        $this->tag->setParameters([
            'item' => 'smelly-cat'
        ]);

        $this->tag->setContent('
            <h2>Remove item from cart?</h2>

            <button type="submit">Update item in cart</submit>
        ');

        $usage = $this->tag->removeItem();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart-items/smelly-cat"', $usage);
    }

    /** @test */
    public function can_output_cart_update_form()
    {
        // TODO: work out the toAugmentedArray issue before writing this test
    }

    /** @test */
    public function can_output_cart_empty_form()
    {
        $this->tag->setParameters([]);

        $this->tag->setContent('
            <h2>Empty cart?</h2>

            <button type="submit">Empty</submit>
        ');

        $usage = $this->tag->empty();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart"', $usage);
    }

    /** @test */
    public function can_get_data_from_cart()
    {
        // TODO, marked as incomplete until we figure out how to store stuff in the session
        $this->markTestIncomplete();

        $cart = Cart::make()->data(['title' => '#0001', 'note' => 'Deliver by front door.'])->save();

        $this->session(['simple-commerce-cart' => $cart->id]);
        $this->tag->setParameters([]);

        $usage = $this->tag->wildcard('note');

        $this->assertSame($usage, 'Deliver by front door.');
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
