<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTags;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;
use Statamic\Facades\Parse;
use Statamic\Facades\Stache;

class CartTagTest extends TestCase
{
    use SetupCollections;

    protected $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();

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
        $product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $product->save();

        $cart = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 5,
                'total'    => 1000,
            ],
        ]);

        $cart->save();

        $this->fakeCart($cart);

        $this->assertStringContainsString('5', $this->tag('{{ sc:cart:items }}{{ quantity }}{{ /sc:cart:items }}'));
    }

    /** @test */
    public function can_get_cart_items_count()
    {
        $productOne = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $productOne->save();

        $productTwo = Product::make()
            ->price(1200)
            ->data([
                'title' => 'Cat Food',
            ]);

        $productTwo->save();

        $cart = Order::make()->lineItems([
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
        ]);

        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('2', (string) $this->tag('{{ sc:cart:count }}'));
    }

    /** @test */
    public function can_get_cart_items_quantity_total()
    {
        $productOne = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $productOne->save();

        $productTwo = Product::make()
            ->price(1200)
            ->data([
                'title' => 'Cat Food',
            ]);

        $productTwo->save();

        $cart = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $productOne->id,
                'quantity' => 7,
                'total'    => 1000,
            ],
            [
                'id'       => Stache::generateId(),
                'product'  => $productTwo->id,
                'quantity' => 4,
                'total'    => 1200,
            ],
        ]);

        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('11', (string) $this->tag('{{ sc:cart:quantityTotal }}'));
    }

    /** @test */
    public function can_get_cart_total()
    {
        $cart = Order::make()->grandTotal(2550);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:total }}'));
    }

    /** @test */
    public function can_get_cart_free_status_if_order_is_free()
    {
        $cart = Order::make()->grandTotal(0);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('Yes', (string) $this->tag('{{ if {sc:cart:free} === true }}Yes{{ else }}No{{ /if }}'));
    }

    /** @test */
    public function can_get_cart_free_status_if_order_is_paid()
    {
        $cart = Order::make()->grandTotal(2550);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('No', (string) $this->tag('{{ if {sc:cart:free} === true }}Yes{{ else }}No{{ /if }}'));
    }

    /** @test */
    public function can_get_cart_grand_total()
    {
        $cart = Order::make()->grandTotal(2550);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:grandTotal }}'));
    }

    /** @test */
    public function can_get_cart_items_total()
    {
        $cart = Order::make()->itemsTotal(2550);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:itemsTotal }}'));
    }

    /** @test */
    public function can_get_cart_shipping_total()
    {
        $cart = Order::make()->shippingTotal(2550);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:shippingTotal }}'));
    }

    /** @test */
    public function can_get_cart_tax_total()
    {
        $cart = Order::make()->taxTotal(2550);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:taxTotal }}'));
    }

    /** @test */
    public function can_get_cart_coupon_total()
    {
        $cart = Order::make()->couponTotal(2550);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('£25.50', (string) $this->tag('{{ sc:cart:couponTotal }}'));
    }

    /** @test */
    public function can_output_add_item_form()
    {
        $product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $product->save();

        $this->tag->setParameters([]);

        $this->tag->setContent('
            <h2>Add Item</h2>

            <input type="hidden" name="product" value="{{ ' . $product->id . ' }}">
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
    public function can_output_update_item_form_with_product_parameter()
    {
        $cart = $this->fakeCart();

        $product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $product->save();

        $lineItem = $cart->withoutRecalculating(function () use (&$cart, $product) {
            return $cart->addLineItem([
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ]);
        });

        $this->tag->setParameters([
            'product' => $product->id,
        ]);

        $this->tag->setContent('
            <h2>Update Item</h2>

            Product: {{ product }}

            <input type="number" name="quantity">
            <button type="submit">Update item in cart</submit>
        ');

        $usage = $this->tag->updateItem();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart-items/' . $lineItem->id . '"', $usage);
        $this->assertStringContainsString('Product: ' . $product->id, $usage);
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
    public function can_output_remove_item_form_with_product_parameter()
    {
        $cart = $this->fakeCart();

        $product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $product->save();

        $lineItem = $cart->withoutRecalculating(function () use (&$cart, $product) {
            return $cart->addLineItem([
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ]);
        });

        $this->tag->setParameters([
            'product' => $product->id,
        ]);

        $this->tag->setContent('
            <h2>Remove item from cart?</h2>

            Product: {{ product }}

            <button type="submit">Update item in cart</submit>
        ');

        $usage = $this->tag->removeItem();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart-items/' . $lineItem->id . '"', $usage);
        $this->assertStringContainsString('Product: ' . $product->id, $usage);
    }

    /** @test */
    public function can_output_cart_update_form()
    {
        $cart = Order::make()->merge([]);

        $cart->save();

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
    public function can_output_if_product_already_exists_in_cart()
    {
        $product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $product->save();

        $cart = Order::make()->lineItems([
            [
                'id' => 'one-two-three',
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

        $cart->save();

        $this->fakeCart($cart);

        $this->tag->setParameters([
            'product' => $product->id,
        ]);

        $usage = $this->tag->alreadyExists();

        $this->assertTrue($usage);
    }

    /** @test */
    public function can_output_if_product_and_variant_already_exists_in_cart()
    {
        $product = Product::make()
            ->data([
                'title' => 'Dog Food',
            ])
            ->productVariants([
                'variants' => [
                    [
                        'name'   => 'Colours',
                        'values' => [
                            'Red',
                        ],
                    ],
                    [
                        'name'   => 'Sizes',
                        'values' => [
                            'Small',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key'     => 'Red_Small',
                        'variant' => 'Red Small',
                        'price'   => 5000,
                    ],
                ],
            ]);

        $product->save();

        $cart = Order::make()->lineItems([
            [
                'id' => 'one-two-three',
                'product' => $product->id,
                'variant' => 'Red_Small',
                'quantity' => 1,
                'total' => 5000,
            ],
        ]);

        $cart->save();

        $this->fakeCart($cart);

        $this->tag->setParameters([
            'product' => $product->id,
            'variant' => 'Red_Small',
        ]);

        $usage = $this->tag->alreadyExists();

        $this->assertTrue($usage);
    }

    /** @test */
    public function can_output_if_product_does_not_already_exists_in_cart()
    {
        $product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $product->save();

        $cart = Order::make()->merge([]);
        $cart->save();

        $this->fakeCart($cart);

        $this->tag->setParameters([
            'product' => $product->id,
        ]);

        $usage = $this->tag->alreadyExists();

        $this->assertFalse($usage);
    }

    /** @test */
    public function cant_output_if_product_does_not_already_exist_in_cart_because_there_is_no_cart()
    {
        $product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Dog Food',
            ]);

        $product->save();

        Session::shouldReceive('get')
            ->with('simple-commerce-cart')
            ->andReturn(null);

        Session::shouldReceive('token')
            ->andReturn('random-token');

        Session::shouldReceive('has')
            ->with('simple-commerce-cart')
            ->andReturn(false);

        Session::shouldReceive('has')
            ->with('errors')
            ->andReturn([]);

        $this->tag->setParameters([
            'product' => $product->id,
        ]);

        $usage = $this->tag->alreadyExists();

        $this->assertFalse($usage);
    }

    /** @test */
    public function can_get_data_from_cart()
    {
        $cart = Order::make()->merge([
            'title' => '#0001',
            'note'  => 'Deliver by front door.',
        ]);

        $cart->save();

        $this->session(['simple-commerce-cart' => $cart->id]);
        $this->tag->setParameters([]);

        $usage = $this->tag->wildcard('note');

        // Statamic 3.3: From 3.3, this will return a Value instance
        $this->assertTrue($usage instanceof \Statamic\Fields\Value || is_string($usage));
        $this->assertSame($usage instanceof \Statamic\Fields\Value ? $usage->value() : $usage, 'Deliver by front door.');
    }

    /**
     * @test
     * https://github.com/doublethreedigital/simple-commerce/pull/650
     */
    public function can_get_data_from_cart_when_method_should_be_converted_to_studly_case()
    {
        $cart = Order::make()->merge([
            'title' => '#0001',
            'note'  => 'Deliver by front door.',
        ])->grandTotal(1590);

        $cart->save();

        $this->session(['simple-commerce-cart' => $cart->id]);
        $this->tag->setParameters([]);

        $usage = $this->tag->wildcard('raw_grand_total');

        // Statamic 3.3: From 3.3, this will return a Value instance
        $this->assertTrue($usage instanceof \Statamic\Fields\Value || is_int($usage));
        $this->assertSame($usage instanceof \Statamic\Fields\Value ? $usage->value() : $usage, 1590);
    }

    /** @test */
    public function cant_get_data_from_cart_if_there_is_no_cart()
    {
        $this->session(['simple-commerce-cart' => null]);
        $this->tag->setParameters([]);

        $usage = $this->tag->wildcard('note');

        // Statamic 3.3: From 3.3, this will return a Value instance
        $this->assertFalse($usage instanceof \Statamic\Fields\Value || is_string($usage));
        $this->assertSame($usage instanceof \Statamic\Fields\Value ? $usage->value() : $usage, null);
    }

    protected function tag($tag)
    {
        return Parse::template($tag, []);
    }

    protected function fakeCart($cart = null)
    {
        if (is_null($cart)) {
            $cart = Order::make()->merge([
                'note' => 'Special note.',
            ]);

            $cart->save();
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

        return $cart;
    }
}
