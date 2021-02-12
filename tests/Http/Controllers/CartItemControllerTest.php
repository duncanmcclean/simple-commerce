<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Stache;

class CartItemControllerTest extends TestCase
{
    use CollectionSetup;

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
            'product' => $product->id,
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
            'slug' => 'dog-food',
            'price' => 1000,
        ])->save();

        $data = [
            'product' => $product->id,
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
    public function can_store_item_with_variant()
    {
        $product = Product::create([
            'title' => 'Dog Food',
            'product_variants' => [
                'variants' => [
                    [
                        'name' => 'Colours',
                        'values' => [
                            'Red',
                        ],
                    ],
                    [
                        'name' => 'Sizes',
                        'values' => [
                            'Small',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key' => 'Red_Small',
                        'price' => 1000,
                    ],
                ],
            ],
        ]);

        $data = [
            'product' => $product->id,
            'variant' => 'Red_Small',
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
            'product' => $product->id,
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
            'product' => $product->id,
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
                    'id' => Stache::generateId(),
                    'product' => $productOne->id,
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
        ]);

        $data = [
            'product' => $productTwo->id,
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
            'product' => $product->id,
            'quantity' => 1,
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
                    'id' => Stache::generateId(),
                    'product' => $productOne->id,
                    'quantity' => 1,
                ]
            ],
        ]);

        $this->assertCount(1, $cart->get('items'));

        $data = [
            'product' => $productTwo->id,
            'quantity' => 1,
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
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
        ]);

        $data = [
            'product' => $product->id,
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
            'title' => 'Dog Food',
            'product_variants' => [
                'variants' => [
                    [
                        'name' => 'Colours',
                        'values' => [
                            'Red',
                        ],
                    ],
                    [
                        'name' => 'Sizes',
                        'values' => [
                            'Small',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key' => 'Red_Small',
                        'price' => 1000,
                    ],
                ],
            ],
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'variant' => [
                        'variant' => 'Red_Small',
                        'product' => $product->id,
                    ],
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
        ]);

        $data = [
            'product' => $product->id,
            'variant' => 'Red_Small',
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
            'title' => 'Dog Food',
            'product_variants' => [
                'variants' => [
                    [
                        'name' => 'Colours',
                        'values' => [
                            'Red',
                        ],
                    ],
                    [
                        'name' => 'Sizes',
                        'values' => [
                            'Small',
                            'Medium',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key' => 'Red_Small',
                        'price' => 1000,
                    ],
                    [
                        'key' => 'Red_Medium',
                        'price' => 1000,
                    ],
                ],
            ],
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'variant' => [
                        'variant' => 'Red_Small',
                        'product' => $product->id,
                    ],
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
        ]);

        $data = [
            'product' => $product->id,
            'variant' => 'Red_Medium',
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
            'product' => $product->id,
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
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
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
                'item' => $cart->data['items'][0]['id']
            ]), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);

        $this->assertSame(2, $cart->data['items'][0]['quantity']);
    }

    /** @test */
    public function can_update_item_and_request_json()
    {
        $product = Product::create([
            'title' => 'Food',
            'slug' => 'food',
            'price' => 1000,
        ])->save();

        $cart = Order::create([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
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
                'item' => $cart->data['items'][0]['id']
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
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
        ]);

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->deleteJson(route('statamic.simple-commerce.cart-items.destroy', [
                'item' => $cart->data['items'][0]['id']
            ]));

        $response->assertJsonStructure([
            'status',
            'message',
            'cart',
        ]);

        $this->assertEmpty($cart->data['items']);
    }
}
