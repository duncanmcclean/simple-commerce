<?php

namespace Tests\Feature\Cart;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartLineItemsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function it_throws_a_not_found_exception_when_no_current_cart_is_set()
    {
        $this
            ->post('/!/simple-commerce/cart/line-items')
            ->assertNotFound();
    }

    #[Test]
    public function it_adds_a_product_to_the_cart()
    {
        $cart = $this->makeCart();
        $product = $this->makeProduct();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals(1, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function it_adds_a_product_to_the_cart_with_data()
    {
        $cart = $this->makeCart();
        $product = $this->makeProduct();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 1,
                'note' => 'This is a present for my friend. Please wrap it in birthday paper.',
                'message' => 'Happy Birthday, friend!',
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals(1, $cart->lineItems()->first()->quantity());

        $this->assertEquals([
            'note' => 'This is a present for my friend. Please wrap it in birthday paper.',
            'message' => 'Happy Birthday, friend!',
        ], $cart->lineItems()->first()->data()->all());
    }

    #[Test]
    public function it_adds_a_variant_product_to_the_cart()
    {
        $cart = $this->makeCart();
        $product = $this->makeVariantProduct();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'variant' => 'Red',
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals('Red', $cart->lineItems()->first()->variant()->key());
        $this->assertEquals(1, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function a_validation_error_is_thrown_when_product_is_invalid()
    {
        $cart = $this->makeCart();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => 'invalid-product',
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('product');

        $this->assertCount(0, $cart->fresh()->lineItems());
    }

    #[Test]
    public function a_validation_error_is_thrown_when_variant_is_invalid()
    {
        $cart = $this->makeCart();
        $product = $this->makeVariantProduct();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'variant' => 'invalid-variant',
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('variant');

        $this->assertCount(0, $cart->fresh()->lineItems());
    }

    #[Test]
    public function it_adds_a_product_to_the_cart_when_stock_is_greater_than_quantity()
    {
        $cart = $this->makeCart();

        $product = $this->makeProduct();
        $product->set('stock', 10)->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 5,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals(5, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function it_doesnt_add_a_product_to_the_cart_when_stock_is_less_than_quantity()
    {
        $cart = $this->makeCart();

        $product = $this->makeProduct();
        $product->set('stock', 3)->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 5,
            ])
            ->assertSessionHasErrors('product');

        $this->assertCount(0, $cart->fresh()->lineItems());
    }

    #[Test]
    public function it_adds_a_variant_product_to_the_cart_when_stock_is_greater_than_quantity()
    {
        $cart = $this->makeCart();
        $product = $this->makeProduct();

        $product->set('product_variants', [
            'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
            'options' => [
                ['key' => 'Red', 'variant' => 'Red', 'price' => 1000, 'stock' => 10],
                ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500, 'stock' => 10],
                ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799, 'stock' => 10],
            ],
        ])->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'variant' => 'Red',
                'quantity' => 5,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals('Red', $cart->lineItems()->first()->variant()->key());
        $this->assertEquals(5, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function it_doesnt_add_a_variant_product_to_the_cart_when_stock_is_less_than_quantity()
    {
        $cart = $this->makeCart();
        $product = $this->makeProduct();

        $product->set('product_variants', [
            'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
            'options' => [
                ['key' => 'Red', 'variant' => 'Red', 'price' => 1000, 'stock' => 3],
                ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500, 'stock' => 3],
                ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799, 'stock' => 3],
            ],
        ])->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'variant' => 'Red',
                'quantity' => 5,
            ])
            ->assertSessionHasErrors('variant');

        $this->assertCount(0, $cart->fresh()->lineItems());
    }

    #[Test]
    public function it_doesnt_add_a_product_to_the_cart_when_the_customer_is_missing_the_prerequisite_product()
    {
        $user = User::make()->save();
        $cart = tap($this->makeCart()->customer($user))->save();

        $product = $this->makeProduct();

        $prerequisiteProduct = $this->makeProduct();
        $product->set('prerequisite_product', $prerequisiteProduct->id())->save();

        Collection::find('products')->entryBlueprint()->ensureField('prerequisite_product', ['type' => 'entries', 'max_items' => 1]);

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('product');

        $this->assertCount(0, $cart->fresh()->lineItems());
    }

    #[Test]
    public function it_adds_a_product_to_the_cart_when_the_customer_has_purchased_the_prerequisite_product()
    {
        $user = User::make()->save();
        $cart = tap($this->makeCart()->customer($user))->save();

        $product = $this->makeProduct();

        $prerequisiteProduct = $this->makeProduct();
        $product->set('prerequisite_product', $prerequisiteProduct->id())->save();

        Collection::find('products')->entryBlueprint()->ensureField('prerequisite_product', ['type' => 'entries', 'max_items' => 1]);

        Order::make()->customer($user)->lineItems([['product' => $prerequisiteProduct->id()]])->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals(1, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function it_updates_the_quantity_when_a_product_is_already_in_the_cart()
    {
        $cart = $this->makeCart();
        $product = $this->makeProduct();

        $cart->lineItems()->create([
            'product' => $product->id(),
            'quantity' => 3,
        ]);

        $cart->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals(4, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function it_updates_the_quantity_when_a_product_is_already_in_the_cart_and_merges_data()
    {
        $cart = $this->makeCart();
        $product = $this->makeProduct();

        $cart->lineItems()->create([
            'product' => $product->id(),
            'quantity' => 3,
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $cart->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 1,
                'foo' => 'baz',
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals(4, $cart->lineItems()->first()->quantity());
        $this->assertEquals('baz', $cart->lineItems()->first()->data()->get('foo'));
        $this->assertEquals('qux', $cart->lineItems()->first()->data()->get('baz'));
    }

    #[Test]
    public function it_updates_the_quantity_when_a_product_variant_is_already_in_the_cart()
    {
        $cart = $this->makeCart();
        $product = $this->makeVariantProduct();

        $cart->lineItems()->create([
            'product' => $product->id(),
            'variant' => 'Red',
            'quantity' => 3,
        ]);

        $cart->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'variant' => 'Red',
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals('Red', $cart->lineItems()->first()->variant()->key());
        $this->assertEquals(4, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function it_doesnt_update_the_quantity_when_a_product_is_already_in_the_cart_but_the_data_is_different()
    {
        config(['simple-commerce.cart.unique_metadata' => true]);

        $cart = $this->makeCart();
        $product = $this->makeProduct();

        $cart->lineItems()->create([
            'product' => $product->id(),
            'quantity' => 1,
            'foo' => 'bar',
        ]);

        $cart->save();

        $this
            ->post('/!/simple-commerce/cart/line-items', [
                'product' => $product->id(),
                'quantity' => 1,
                'foo' => 'baz',
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(2, $cart->lineItems());

        $this->assertEquals($product->id(), $cart->lineItems()->first()->product()->id());
        $this->assertEquals(1, $cart->lineItems()->first()->quantity());
        $this->assertEquals('bar', $cart->lineItems()->first()->data()->get('foo'));

        $this->assertEquals($product->id(), $cart->lineItems()->last()->product()->id());
        $this->assertEquals(1, $cart->lineItems()->last()->quantity());
        $this->assertEquals('baz', $cart->lineItems()->last()->data()->get('foo'));
    }

    #[Test]
    public function it_throws_a_not_found_exception_when_line_item_cant_be_found()
    {
        $this->makeCart();

        $this->patch('/!/simple-commerce/cart/line-items/foo')->assertNotFound();
    }

    #[Test]
    public function it_updates_a_line_item()
    {
        $cart = $this->makeCartWithLineItems();

        $this
            ->patch('/!/simple-commerce/cart/line-items/line-item-1', [
                'quantity' => 3,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals(3, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function it_updates_a_line_item_with_a_different_variant()
    {
        $cart = $this->makeCart();
        $product = $this->makeVariantProduct();

        $cart->lineItems()->create([
            'id' => 'line-item-1',
            'product' => $product->id(),
            'variant' => 'Red',
            'quantity' => 1,
        ]);

        $this
            ->patch('/!/simple-commerce/cart/line-items/line-item-1', [
                'variant' => 'Yellow',
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals('Yellow', $cart->lineItems()->first()->variant()->key());
        $this->assertEquals(1, $cart->lineItems()->first()->quantity());
    }

    #[Test]
    public function it_updates_a_line_item_and_merges_data()
    {
        $cart = $this->makeCartWithLineItems();

        $this
            ->patch('/!/simple-commerce/cart/line-items/line-item-1', [
                'quantity' => 1,
                'foo' => 'baz',
            ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $cart = $cart->fresh();

        $this->assertCount(1, $cart->lineItems());

        $this->assertEquals(1, $cart->lineItems()->first()->quantity());
        $this->assertEquals('baz', $cart->lineItems()->first()->data()->get('foo'));
        $this->assertEquals('qux', $cart->lineItems()->first()->data()->get('baz'));
    }

    #[Test]
    public function it_removes_a_line_item()
    {
        $cart = $this->makeCartWithLineItems();

        $this
            ->delete('/!/simple-commerce/cart/line-items/line-item-1')
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());

        $this->assertCount(0, $cart->fresh()->lineItems());
    }

    protected function makeCart()
    {
        $cart = Cart::make()->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com']);
        $cart->save();

        Cart::setCurrent($cart);

        return $cart;
    }

    protected function makeCartWithLineItems()
    {
        $cart = $this->makeCart()->lineItems([
            [
                'id' => 'line-item-1',
                'product' => $this->makeProduct()->id(),
                'quantity' => 1,
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ]);

        $cart->save();

        return $cart;
    }

    protected function makeProduct()
    {
        Collection::make('products')->save();

        return tap(Entry::make()->collection('products'))->save();
    }

    protected function makeVariantProduct()
    {
        Collection::make('products')->save();

        $product = Entry::make()
            ->collection('products')
            ->set('product_variants', [
                'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
                'options' => [
                    ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                    ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500],
                    ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799],
                ],
            ]);

        $product->save();

        return $product;
    }
}