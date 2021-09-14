<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
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
        $this->fakeCart();

        $this->assertSame('Special note.', (string) $this->tag('{{ sc:cart }}{{ note }}{{ /sc:cart }}'));
        $this->assertSame('false', (string) $this->tag('{{ sc:cart }}{{ if {is_paid} }}true{{ else }}false{{ /if }}{{ /sc:cart }}'));
    }

    /** @test */
    public function user_has_a_cart_if_cart_does_not_exist()
    {
        $this->assertSame('No cart', (string) $this->tag('{{ if sc:cart:has }}Has cart{{ else }}No cart{{ /if }}'));
    }

    /** @test */
    public function user_has_a_cart_if_cart_exists()
    {
        $this->fakeCart();

        $this->assertSame('Has cart', (string) $this->tag('{{ if {sc:cart:has} === true }}Has cart{{ else }}No cart{{ /if }}'));
    }

    /** @test */
    public function can_get_cart_items()
    {
        // TODO: work out issues with toAugmentedArray() playing up in tests
        $this->markTestIncomplete();

        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 5,
                    'total'    => 1000,
                ],
            ],
        ]);

        $this->fakeCart($cart);

        $this->assertStringContainsString('5', $this->tag('{{ sc:cart:items }}{{ quantity }}{{ /sc:cart:items }}'));
    }

    /** @test */
    public function can_get_cart_items_count()
    {
        $productOne = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $productTwo = Product::create([
            'title' => 'Cat Food',
            'price' => 1200,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $productOne->id,
                    'quantity' => 5,
                    'total'    => 1000,
                ],
                [
                    'id'       => Stache::generateId(),
                    'product'  => $productTwo->id,
                    'quantity' => 5,
                    'total'    => 1200,
                ],
            ],
        ]);

        $this->fakeCart($cart);

        $this->assertSame('2', (string) $this->tag('{{ sc:cart:count }}'));
    }

    /** @test */
    public function can_get_cart_items_quantity()
    {
        $productOne = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $productTwo = Product::create([
            'title' => 'Cat Food',
            'price' => 1200,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $productOne->id,
                    'quantity' => 5,
                    'total'    => 1000,
                ],
                [
                    'id'       => Stache::generateId(),
                    'product'  => $productTwo->id,
                    'quantity' => 5,
                    'total'    => 1200,
                ],
            ],
        ]);

        $this->fakeCart($cart);

        $this->assertSame('10', (string) $this->tag('{{ sc:cart:quantity }}'));
    }

    /** @test */
    public function can_get_cart_total()
    {
        $cart = Order::create([
            'grand_total' => 2550,
        ]);

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:total }}'));
    }

    /** @test */
    public function can_get_cart_grand_total()
    {
        $cart = Order::create([
            'grand_total' => 2550,
        ]);

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:grandTotal }}'));
    }

    /** @test */
    public function can_get_cart_items_total()
    {
        $cart = Order::create([
            'items_total' => 2550,
        ]);

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:itemsTotal }}'));
    }

    /** @test */
    public function can_get_cart_shipping_total()
    {
        $cart = Order::create([
            'shipping_total' => 2550,
        ]);

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:shippingTotal }}'));
    }

    /** @test */
    public function can_get_cart_tax_total()
    {
        $cart = Order::create([
            'tax_total' => 2550,
        ]);

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:taxTotal }}'));
    }

    /** @test */
    public function can_get_cart_coupon_total()
    {
        $cart = Order::create([
            'coupon_total' => 2550,
        ]);

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:couponTotal }}'));
    }

    /** @test */
    public function can_output_add_item_form()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

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
            'item' => 'absolute-load-of-jiberish',
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
            'item' => 'smelly-cat',
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
        $cart = Order::create([]);

        $this->fakeCart($cart);

        $this->tag->setParameters([]);

        $this->tag->setContent('
            <h2>Update cart</h2>

            <input name="name">
            <input name="email">

            <button type="submit">Update cart</submit>
        ');

        $usage = $this->tag->update();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart"', $usage);
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
        $cart = Order::create([
            'title' => '#0001',
            'note'  => 'Deliver by front door.',
        ]);

        $this->session(['simple-commerce-cart' => $cart->id]);
        $this->tag->setParameters([]);

        $usage = $this->tag->wildcard('note');

        $this->assertSame($usage, 'Deliver by front door.');
    }

    /** @test */
    public function can_get_augmented_value_from_cart_data()
    {
        // TODO: Write a test to ensure we can grab the value of an augmented field with the `wildcard` magic.
        $this->markTestIncomplete();
    }

    protected function tag($tag)
    {
        return Parse::template($tag, []);
    }

    protected function fakeCart($cart = null)
    {
        if (is_null($cart)) {
            $cart = Order::create([
                'note' => 'Special note.',
            ]);
        }

        Session::shouldReceive('get')
            ->with('simple-commerce-cart')
            ->andReturn($cart->id);

        Session::shouldReceive('token')
            ->andReturn('random-token');

        Session::shouldReceive('has')
            ->with('simple-commerce-cart')
            ->andReturn(true);

        Session::shouldReceive('has')
            ->with('errors')
            ->andReturn([]);
    }
}
