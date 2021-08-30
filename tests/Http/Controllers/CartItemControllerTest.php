<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\Stache;

class CartItemControllerTest extends TestCase
{
    use SetupCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function can_store_item()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function can_store_item_and_request_json()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'slug'  => 'dog-food',
            'price' => 1000,
        ])->save();

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->postJson(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertJsonStructure([
            'status',
            'message',
            'cart',
        ]);

        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function can_store_item_with_extra_data()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
            'foo' => 'bar',
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));

        $this->assertArrayHasKey('foo', $cart->lineItems()->first()['metadata']);
    }

    /** @test */
    public function can_store_item_and_ensure_custom_form_request_is_used()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $data = [
            '_request' => CartItemStoreFormRequest::class,
            'product'  => $product->id,
            'quantity' => 1,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHasErrors('smth');
    }

    /** @test */
    public function can_store_item_with_variant()
    {
        $product = Product::create([
            'title'            => 'Dog Food',
            'product_variants' => [
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
                        'price'   => 1000,
                    ],
                ],
            ],
        ]);

        $data = [
            'product'  => $product->id,
            'variant'  => 'Red_Small',
            'quantity' => 1,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function can_store_item_with_existing_cart()
    {
        $product = Product::create([
            'title' => 'Cat Food',
            'price' => 1000,
        ]);

        $cart = Order::create();

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');
        $this->assertSame(1000, $cart->data['items_total']);

        $cart = $cart->find($cart->id);

        $this->assertSame(session()->get('simple-commerce-cart'), $cart->id);
        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function can_store_item_and_ensure_the_quantity_is_not_more_than_stock()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1567,
            'stock' => 2,
        ]);

        $cart = Order::create();

        $data = [
            'product'  => $product->id,
            'quantity' => 5,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.store'), $data)
            ->assertSessionHasErrors();
    }

    /** @test */
    public function can_store_item_and_ensure_existing_items_are_not_overwritten()
    {
        $productOne = Product::create([
            'title' => 'Rabbit Food',
            'price' => 1000,
        ]);

        $productTwo = Product::create([
            'title' => 'Fish Food',
            'price' => 2300,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $productOne->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $data = [
            'product'  => $productTwo->id,
            'quantity' => 1,
        ];

        $response = $this
            ->from('/products/'.$productTwo->slug)
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$productTwo->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = $cart->find($cart->id);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertSame(session()->get('simple-commerce-cart'), $cart->id);
        // $this->assertSame(3300, $cart->data['items_total']);

        $this->assertStringContainsString($productOne->id, json_encode($cart->data['items']));
        $this->assertStringContainsString($productTwo->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function can_store_item_with_custom_redirect_url()
    {
        $product = Product::create([
            'title' => 'Horse Food',
            'price' => 1000,
        ]);

        $data = [
            'product'   => $product->id,
            'quantity'  => 1,
            '_redirect' => '/checkout',
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/checkout');
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function can_store_item_with_name_and_email()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
            'name' => 'Michael Scott',
            'email' => 'michael@scott.net',
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));

        // Assert customer has been created with provided details
        $this->assertNotNull($cart->get('customer'));

        $this->assertSame($cart->customer()->name(), 'Michael Scott');
        $this->assertSame($cart->customer()->email(), 'michael@scott.net');
    }

    /** @test */
    public function can_store_item_with_only_email()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
            'email' => 'donald@duck.disney',
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));

        // Assert customer has been created with provided details
        $this->assertNotNull($cart->get('customer'));

        $this->assertSame($cart->customer()->name(), 'donald@duck.disney');
        $this->assertSame($cart->customer()->email(), 'donald@duck.disney');
    }

    /** @test */
    public function can_store_item_with_customer_already_in_present_in_order()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $customer = Customer::create([
            'name' => 'Goofy',
            'email' => 'goofy@clubhouse.disney',
        ]);

        $order = Order::create([
            'customer' => $customer->id,
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
        ];

        $response = $this
            ->withSession(['simple-commerce-cart' => $order->id()])
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));

        // Assert customer has been created with provided details
        $this->assertNotNull($cart->get('customer'));

        $this->assertSame($cart->customer()->name(), 'Goofy');
        $this->assertSame($cart->customer()->email(), 'goofy@clubhouse.disney');
    }

    /** @test */
    public function can_store_item_with_customer_present_in_request()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $customer = Customer::create([
            'name' => 'Pluto',
            'email' => 'pluto@clubhouse.disney',
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
            'customer' => $customer->id,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));

        // Assert customer has been created with provided details
        $this->assertNotNull($cart->get('customer'));

        $this->assertSame($cart->customer()->name(), 'Pluto');
        $this->assertSame($cart->customer()->email(), 'pluto@clubhouse.disney');
    }

    /** @test */
    public function can_store_item_where_product_requires_prerequisite_product_and_customer_has_purchased_prerequisite_product()
    {
        $prerequisiteProduct = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
            'prerequisite_product' => $prerequisiteProduct->id,
        ]);

        $customer = Customer::create([
            'name' => 'Test Test',
            'email' => 'test@test.test',
        ]);

        Order::create([
            'items' => [
                [
                    'id' => 'smth',
                    'product' => $prerequisiteProduct->id,
                    'quantity' => 1,
                    'total' => 1599,
                ],
            ],
            'items_total' => 1599,
            'grand_total' => 1599,
            'customer' => $customer->id,
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
            'customer' => $customer->id,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(1599, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($product->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function cant_store_item_where_product_requires_prerequisite_product_and_no_customer_available()
    {
        $prerequisiteProduct = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
            'prerequisite_product' => $prerequisiteProduct->id,
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $response->assertSessionHasErrors();

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertNotSame(2000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringNotContainsString($product->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function cant_store_item_where_product_requires_prerequisite_product_and_customer_has_not_purchased_prerequisite_product()
    {
        $prerequisiteProduct = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
            'prerequisite_product' => $prerequisiteProduct->id,
        ]);

        $customer = Customer::create([
            'name' => 'Test Test',
            'email' => 'test@test.test',
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
            'customer' => $customer->id,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHas('simple-commerce-cart');

        $response->assertSessionHasErrors();

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertNotSame(2000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringNotContainsString($product->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function can_add_second_item_to_a_cart_with_an_existing_item()
    {
        $productOne = Product::create([
            'title' => 'Product One',
            'price' => 1000,
        ]);

        $productTwo = Product::create([
            'title' => 'Product Two',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $productOne->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $this->assertCount(1, $cart->get('items'));

        $data = [
            'product'   => $productTwo->id,
            'quantity'  => 1,
            '_redirect' => '/checkout',
        ];

        $response = $this
            ->from('/products/'.$productTwo->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/checkout');
        $response->assertSessionHas('simple-commerce-cart');

        $cart = Order::find(session()->get('simple-commerce-cart'));

        $this->assertSame(2000, $cart->data['items_total']);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertStringContainsString($productTwo->id, json_encode($cart->data['items']));
    }

    /** @test */
    public function can_store_a_product_that_is_already_in_the_cart()
    {
        $product = Product::create([
            'title' => 'Horse Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => 1,
        ];

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.store'), $data)
            ->assertRedirect();

        $cart = $cart->find($cart->id);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertSame(1, count($cart->data['items']));
        $this->assertSame(2, $cart->data['items'][0]['quantity']);
    }

    /** @test */
    public function can_store_a_variant_that_is_already_in_the_cart()
    {
        $product = Product::create([
            'title'            => 'Dog Food',
            'product_variants' => [
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
                        'price'   => 1000,
                    ],
                ],
            ],
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'      => Stache::generateId(),
                    'product' => $product->id,
                    'variant' => [
                        'variant' => 'Red_Small',
                        'product' => $product->id,
                    ],
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $data = [
            'product'  => $product->id,
            'variant'  => 'Red_Small',
            'quantity' => 4,
        ];

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.store'), $data)
            ->assertRedirect();

        $cart = $cart->find($cart->id);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertSame(1, count($cart->data['items']));
        $this->assertSame(5, $cart->data['items'][0]['quantity']);
    }

    /** @test */
    public function can_store_variant_of_a_product_that_has_another_variant_that_is_in_the_cart()
    {
        $product = Product::create([
            'title'            => 'Dog Food',
            'product_variants' => [
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
                            'Medium',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key'     => 'Red_Small',
                        'variant' => 'Red Small',
                        'price'   => 1000,
                    ],
                    [
                        'key'     => 'Red_Medium',
                        'variant' => 'Red Medium',
                        'price'   => 1000,
                    ],
                ],
            ],
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'      => Stache::generateId(),
                    'product' => $product->id,
                    'variant' => [
                        'variant' => 'Red_Small',
                        'product' => $product->id,
                    ],
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $data = [
            'product'  => $product->id,
            'variant'  => 'Red_Medium',
            'quantity' => 1,
        ];

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.store'), $data)
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $cart = $cart->find($cart->id);

        $this->assertArrayHasKey('items', $cart->data);
        $this->assertSame(2, count($cart->data['items']));
    }

    /** @test */
    public function cant_store_item_with_negative_quantity()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'price' => 1000,
        ]);

        $data = [
            'product'  => $product->id,
            'quantity' => -1,
        ];

        $response = $this
            ->from('/products/'.$product->slug)
            ->post(route('statamic.simple-commerce.cart-items.store'), $data);

        $response->assertRedirect('/products/'.$product->slug);
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function can_update_item()
    {
        $product = Product::create([
            'title' => 'Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $data = [
            'quantity' => 2,
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.update', [
                'item' => $cart->data['items'][0]['id'],
            ]), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);

        $this->assertSame(2, $cart->data['items'][0]['quantity']);
    }

    /** @test */
    public function can_update_item_and_ensure_custom_form_request_is_used()
    {
        $product = Product::create([
            'title' => 'Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $data = [
            '_request' => CartItemUpdateFormRequest::class,
            'quantity' => 2,
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.update', [
                'item' => $cart->data['items'][0]['id'],
            ]), $data);

        $response->assertRedirect('/cart');
        $response->assertSessionHasErrors('coolzies');
    }

    /** @test */
    public function cant_update_item_with_zero_item_quantity()
    {
        $product = Product::create([
            'title' => 'Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $data = [
            'quantity' => 0,
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.update', [
                'item' => $cart->data['items'][0]['id'],
            ]), $data);

        $response->assertSessionHasErrors();

        $cart->find($cart->id);

        $this->assertSame(1, $cart->data['items'][0]['quantity']);
    }

    /** @test */
    public function can_update_item_with_extra_data()
    {
        $product = Product::create([
            'title' => 'Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $data = [
            'gift_note' => 'Have a good birthday!',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.update', [
                'item' => $cart->data['items'][0]['id'],
            ]), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);

        $this->assertSame($cart->lineItems()->count(), 1);
        $this->assertArrayHasKey('metadata', $cart->lineItems()->first());
        $this->assertArrayNotHasKey('gift_note', $cart->lineItems()->first());
    }

    /** @test */
    public function can_update_item_with_extra_data_and_ensure_existing_metadata_isnt_overwritten()
    {
        $product = Product::create([
            'title' => 'Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                    'metadata' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ]);

        $data = [
            'bar' => 'baz',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart-items.update', [
                'item' => $cart->data['items'][0]['id'],
            ]), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);

        $this->assertSame($cart->lineItems()->count(), 1);
        $this->assertArrayHasKey('metadata', $cart->lineItems()->first());

        $this->assertArrayNotHasKey('foo', $cart->lineItems()->first());
        $this->assertArrayNotHasKey('bar', $cart->lineItems()->first());

        $this->assertSame($cart->data['items'][0]['metadata']['foo'], 'bar');
        $this->assertSame($cart->data['items'][0]['metadata']['bar'], 'baz');
    }

    /** @test */
    public function can_update_item_and_request_json()
    {
        $product = Product::create([
            'title' => 'Food',
            'slug'  => 'food',
            'price' => 1000,
        ])->save();

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ])->save();

        $data = [
            'quantity' => 2,
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->postJson(route('statamic.simple-commerce.cart-items.update', [
                'item' => $cart->data['items'][0]['id'],
            ]), $data);

        $response->assertJsonStructure([
            'status',
            'message',
            'cart',
        ]);

        $cart->find($cart->id);

        $this->assertSame(2, $cart->data['items'][0]['quantity']);
    }

    /** @test */
    public function can_destroy_item()
    {
        $product = Product::create([
            'title' => 'Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ]);

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->deleteJson(route('statamic.simple-commerce.cart-items.destroy', [
                'item' => $cart->data['items'][0]['id'],
            ]));

        $response->assertJsonStructure([
            'status',
            'message',
            'cart',
        ]);

        $this->assertEmpty($cart->data['items']);
    }
}

class CartItemStoreFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'smth' => ['required', 'string'],
        ];
    }
}

class CartItemUpdateFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'coolzies' => ['required', 'string'],
        ];
    }
}
