<?php

use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CartItemStoreFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CartItemUpdateFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Stache;
use Statamic\Facades\User;

uses(SetupCollections::class);
uses(RefreshContent::class);

beforeEach(function () {
    $this->useBasicTaxEngine();
});

test('can store item', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);
});

test('can store item and request json', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->postJson(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertJsonStructure([
        'status',
        'message',
        'cart',
    ]);

    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);
});

test('can store item with extra data', function () {
    Config::set('simple-commerce.field_whitelist.line_items', ['foo']);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'foo' => 'bar',
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    $this->assertArrayHasKey('foo', $cart->lineItems()->first()->metadata()->toArray());
});

test('cant store item with extra data if fields not whitelisted in config', function () {
    Config::set('simple-commerce.field_whitelist.line_items', []);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'foo' => 'bar',
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    $this->assertArrayNotHasKey('foo', $cart->lineItems()->first()->metadata());
});

test('can store item and ensure custom form request is used', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        '_request' => encrypt(CartItemStoreFormRequest::class),
        'product' => $product->id,
        'quantity' => 1,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHasErrors('smth');
});

test('can store item with variant', function () {
    $product = Product::make()
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ])
        ->productVariants([
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
                    'variant' => 'Red Small',
                    'price' => 1000,
                ],
            ],
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'variant' => 'Red_Small',
        'quantity' => 1,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);
});

// https://github.com/duncanmcclean/simple-commerce/issues/867
test('cant store item without variant when product is a variant product', function () {
    $product = Product::make()
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ])
        ->productVariants([
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
                    'variant' => 'Red Small',
                    'price' => 1000,
                ],
            ],
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
    ];

    $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data)
        ->assertSessionHasErrors('variant');
});

test('can store item with metadata where metadata is unique', function () {
    Config::set('simple-commerce.cart.unique_metadata', true);
    Config::set('simple-commerce.field_whitelist.line_items', ['foo', 'barz']);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => 'smth',
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
                'metadata' => [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
            ],
        ])
        ->grandTotal(1000)
        ->itemsTotal(1000);

    $cart->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'foo' => 'bar',
        'barz' => 'baz',
    ];

    $response = $this
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(2000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    expect($cart->lineItems()->first()->quantity())->toBe(1);
    $this->assertArrayHasKey('foo', $cart->lineItems()->first()->metadata()->toArray());
    $this->assertArrayHasKey('bar', $cart->lineItems()->first()->metadata()->toArray());
    $this->assertArrayNotHasKey('barz', $cart->lineItems()->first()->metadata()->toArray());

    expect($cart->lineItems()->first()->quantity())->toBe(1);
    $this->assertArrayHasKey('foo', $cart->lineItems()->last()->metadata()->toArray());
    $this->assertArrayNotHasKey('bar', $cart->lineItems()->last()->metadata()->toArray());
    $this->assertArrayHasKey('barz', $cart->lineItems()->last()->metadata()->toArray());

    Config::set('simple-commerce.cart.unique_metadata', false);
});

test('can store item with metadata where metadata is not unique', function () {
    Config::set('simple-commerce.cart.unique_metadata', true);
    Config::set('simple-commerce.field_whitelist.line_items', ['foo', 'bar']);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => 'smth',
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
                'metadata' => [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
            ],
        ])
        ->grandTotal(1000)
        ->itemsTotal(1000);

    $cart->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'foo' => 'bar',
        'bar' => 'baz',
    ];

    $response = $this
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(2000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    expect($cart->lineItems()->first()->quantity())->toBe(2);

    $this->assertArrayHasKey('foo', $cart->lineItems()->first()->metadata()->toArray());
    $this->assertArrayHasKey('bar', $cart->lineItems()->first()->metadata()->toArray());

    Config::set('simple-commerce.cart.unique_metadata', false);
});

test('can store item with existing cart', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Cat Food',
            'slug' => 'cat-food',
        ]);

    $product->save();

    $cart = Order::make();
    $cart->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = $cart->fresh();

    expect($cart->itemsTotal())->toBe(1000);
    expect($cart->id)->toBe(session()->get('simple-commerce-cart'));
    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);
});

test('can store item and ensure the quantity is not more than stock', function () {
    $product = Product::make()
        ->price(1567)
        ->stock(2)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $cart = Order::make();
    $cart->save();

    $data = [
        'product' => $product->id,
        'quantity' => 5,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.store'), $data)
        ->assertSessionHasErrors();
});

test('can store item with variant and ensure the quantity is not more than stock', function () {
    $product = Product::make()
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ])
        ->productVariants([
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
                    'variant' => 'Red Small',
                    'price' => 1000,
                    'stock' => 2,
                ],
            ],
        ]);

    $product->save();

    $cart = Order::make();
    $cart->save();

    $data = [
        'product' => $product->id,
        'variant' => 'Red_Small',
        'quantity' => 5,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.store'), $data)
        ->assertSessionHasErrors();
});

test('cant store item when standard product has no stock', function () {
    $product = Product::make()
        ->price(1500)
        ->stock(0)
        ->data([
            'title' => 'Tiger Food',
            'slug' => 'tiger-food',
        ]);

    $product->save();

    $cart = Order::make();
    $cart->save();

    $data = [
        'product' => $product->id,
        'quantity' => 2,
    ];

    $this
        ->from('/products/'.$product->get('slug'))
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.store'), $data)
        ->assertSessionHasErrors();

    $cart->fresh();

    expect(0)->toBe($cart->lineItems()->count());
});

test('cant store item when variant product has no stock', function () {
    $product = Product::make()
        ->productVariants([
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
                    'variant' => 'Red Small',
                    'price' => 1000,
                    'stock' => 0,
                ],
            ],
        ]);

    $product->save();

    $cart = Order::make();
    $cart->save();

    $data = [
        'product' => $product->id,
        'variant' => 'Red_Small',
        'quantity' => 2,
    ];

    $this
        ->from('/products/'.$product->get('slug'))
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.store'), $data)
        ->assertSessionHasErrors();

    $cart->fresh();

    expect(0)->toBe($cart->lineItems()->count());
});

test('can store item and ensure existing items are not overwritten', function () {
    $productOne = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Rabbit Food',
            'slug' => 'rabbit-food',
        ]);

    $productOne->save();

    $productTwo = Product::make()
        ->price(2300)
        ->data([
            'title' => 'Fish Food',
            'slug' => 'fish-food',
        ]);

    $productTwo->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $productOne->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'product' => $productTwo->id,
        'quantity' => 1,
    ];

    $response = $this
        ->from('/products/'.$productTwo->get('slug'))
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$productTwo->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart->fresh();

    expect($cart->id)->toBe(session()->get('simple-commerce-cart'));
    // $this->assertSame(3300, $cart->itemsTotal());

    expect(json_encode($cart->lineItems()->toArray()))->toContain($productOne->id);
    expect(json_encode($cart->lineItems()->toArray()))->toContain($productTwo->id);
});

test('can store item with custom redirect url', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Horse Food',
            'slug' => 'horse-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        '_redirect' => encrypt('/checkout'),
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/checkout');
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);
});

test('can store item with name and email', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'name' => 'Michael Scott',
        'email' => 'michael@scott.net',
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('Michael Scott')->toBe($cart->customer()->name());
    expect('michael@scott.net')->toBe($cart->customer()->email());
});

test('can store item with first name and last name and email', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'first_name' => 'Michael',
        'last_name' => 'Scott',
        'email' => 'michael@scott.net',
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('Michael Scott')->toBe($cart->customer()->name());
    expect('michael@scott.net')->toBe($cart->customer()->email());
});

test('cant store item with email that contains spaces', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'name' => 'Spud Man',
        'email' => 'spud man@potato.net',
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data)
        ->assertSessionHasErrors()
        ->assertSessionMissing('simple-commerce-cart');

    try {
        Customer::findByEmail('spud man@potato.net');

        expect(false)->toBeTrue();
    } catch (CustomerNotFound $e) {
        expect(true)->toBeTrue();
    }
});

test('can store item with only email', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'email' => 'donald@duck.disney',
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect(null)->toBe($cart->customer()->name());
    expect('donald@duck.disney')->toBe($cart->customer()->email());
});

test('can store item with customer already in present in order', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $customer = Customer::make()
        ->email('goofy@clubhouse.disney')
        ->data([
            'name' => 'Goofy',
        ]);

    $customer->save();

    $order = Order::make()->customer($customer->id);
    $order->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
    ];

    $response = $this
        ->withSession(['simple-commerce-cart' => $order->id()])
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('Goofy')->toBe($cart->customer()->name());
    expect('goofy@clubhouse.disney')->toBe($cart->customer()->email());
});

test('can store item with customer already in present in order and ensure the customer email is updated', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $customer = Customer::make()
        ->email('goofy@clubhouse.disney')
        ->data([
            'name' => 'Goofy',
        ]);

    $customer->save();

    $order = Order::make()->customer($customer->id);
    $order->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'customer' => ['email' => 'goofy@mickeymouse.clubhouse'],
    ];

    $response = $this
        ->withSession(['simple-commerce-cart' => $order->id()])
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('Goofy')->toBe($cart->customer()->name());
    expect('goofy@mickeymouse.clubhouse')->toBe($cart->customer()->email());
});

test('can store item with customer present in request', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $customer = Customer::make()
        ->email('pluto@clubhouse.disney')
        ->data([
            'name' => 'Pluto',
        ]);

    $customer->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'customer' => $customer->id,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('Pluto')->toBe($cart->customer()->name());
    expect('pluto@clubhouse.disney')->toBe($cart->customer()->email());
});

test('can store item with customer array', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'customer' => [
            'name' => 'James',
            'email' => 'james@example.com',
        ],
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('James')->toBe($cart->customer()->name());
    expect('james@example.com')->toBe($cart->customer()->email());
});

test('can store item with customer array and existing customer', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $customer = Customer::make()
        ->email('pluto@clubhouse.disney')
        ->data([
            'name' => 'Pluto',
        ]);

    $customer->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'customer' => [
            'email' => 'pluto@clubhouse.disney',
        ],
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('Pluto')->toBe($cart->customer()->name());
    expect('pluto@clubhouse.disney')->toBe($cart->customer()->email());
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/658
 */
test('can store item with customer array and additional customer information', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'dob',
    ]);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'customer' => [
            'name' => 'James',
            'email' => 'james@example.com',
            'dob' => '01/01/2000',
        ],
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('James')->toBe($cart->customer()->name());
    expect('james@example.com')->toBe($cart->customer()->email());
    expect('01/01/2000')->toBe($cart->customer()->get('dob'));
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/658
 */
test('can store item with customer array and existing customer and additional customer information', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'dob',
    ]);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $customer = Customer::make()
        ->email('pluto@clubhouse.disney')
        ->data([
            'name' => 'Pluto',
        ]);

    $customer->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'customer' => [
            'email' => 'pluto@clubhouse.disney',
            'dob' => '01/01/2000',
        ],
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id);

    // Assert customer has been created with provided details
    $this->assertNotNull($cart->customer());

    expect('Pluto')->toBe($cart->customer()->name());
    expect('pluto@clubhouse.disney')->toBe($cart->customer()->email());
    expect('01/01/2000')->toBe($cart->customer()->get('dob'));
});

test('can store item and use logged in user as customer', function () {
    setupUserCustomerRepository();

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $user = User::make()->email('james@example.com')->set('name', 'James Test');
    $user->save();

    $response = $this
        ->actingAs($user)
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), [
            'product' => $product->id,
            'quantity' => 1,
        ]);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    $this->assertNotNull($cart->customer());
    expect($user->id)->toBe($cart->customer()->id());
    expect($user->name)->toBe($cart->customer()->name());
    expect($user->email)->toBe($cart->customer()->email());

    tearDownUserCustomerRepository();
});

test('can store item where product requires prerequisite product and customer has purchased prerequisite product', function () {
    $prerequisiteProduct = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $prerequisiteProduct->save();

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'prerequisite_product' => $prerequisiteProduct->id,
        ]);

    $product->save();

    $customer = Customer::make()
        ->email('test@test.test')
        ->data([
            'name' => 'Test Test',
        ]);

    $customer->save();

    Order::make()
        ->lineItems([
            [
                'id' => 'smth',
                'product' => $prerequisiteProduct->id,
                'quantity' => 1,
                'total' => 1599,
            ],
        ])
        ->grandTotal(1599)
        ->itemsTotal(1599)
        ->customer($customer->id)
        ->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'customer' => $customer->id,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(1599);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($product->id());
})->skip();

test('cant store item where product requires prerequisite product and no customer available', function () {
    $prerequisiteProduct = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $prerequisiteProduct->save();

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'prerequisite_product' => $prerequisiteProduct->id,
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $response->assertSessionHasErrors();

    $cart = Order::find(session()->get('simple-commerce-cart'));

    $this->assertNotSame(2000, $cart->itemsTotal());

    $this->assertStringNotContainsString($product->id, json_encode($cart->lineItems()->toArray()));
});

test('cant store item where product requires prerequisite product and customer has not purchased prerequisite product', function () {
    $prerequisiteProduct = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $prerequisiteProduct->save();

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'prerequisite_product' => $prerequisiteProduct->id,
        ]);

    $product->save();

    $customer = Customer::make()
        ->email('test@test.test')
        ->data([
            'name' => 'Test Test',
        ]);

    $customer->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
        'customer' => $customer->id,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHas('simple-commerce-cart');

    $response->assertSessionHasErrors();

    $cart = Order::find(session()->get('simple-commerce-cart'));

    $this->assertNotSame(2000, $cart->itemsTotal());

    $this->assertStringNotContainsString($product->id, json_encode($cart->lineItems()->toArray()));
});

test('can add second item to a cart with an existing item', function () {
    $productOne = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Product One',
        ]);

    $productOne->save();

    $productTwo = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Product Two',
        ]);

    $productTwo->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $productOne->id,
                'quantity' => 1,
            ],
        ]);

    $cart->save();

    expect($cart->lineItems()->toArray())->toHaveCount(1);

    $data = [
        'product' => $productTwo->id,
        'quantity' => 1,
        '_redirect' => encrypt('/checkout'),
    ];

    $response = $this
        ->from('/products/'.$productTwo->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/checkout');
    $response->assertSessionHas('simple-commerce-cart');

    $cart = Order::find(session()->get('simple-commerce-cart'));

    expect($cart->itemsTotal())->toBe(2000);

    expect(json_encode($cart->lineItems()->toArray()))->toContain($productTwo->id);
})->skip();

test('can store a product that is already in the cart', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Horse Food',
            'slug' => 'horse-food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'product' => $product->id,
        'quantity' => 1,
    ];

    $response = $this
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.store'), $data)
        ->assertRedirect();

    $cart = $cart->fresh();

    expect($cart->lineItems()->count())->toBe(1);
    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(2);
});

test('can store a variant that is already in the cart', function () {
    $product = Product::make()
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ])
        ->productVariants([
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
                    'variant' => 'Red Small',
                    'price' => 1000,
                ],
            ],
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
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
        ]);

    $cart->save();

    $data = [
        'product' => $product->id,
        'variant' => 'Red_Small',
        'quantity' => 4,
    ];

    $response = $this
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.store'), $data)
        ->assertRedirect();

    $cart = $cart->fresh();

    expect($cart->lineItems()->count())->toBe(1);
    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(5);
});

test('can store variant of a product that has another variant that is in the cart', function () {
    $product = Product::make()
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ])
        ->productVariants([
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
                    'variant' => 'Red Small',
                    'price' => 1000,
                ],
                [
                    'key' => 'Red_Medium',
                    'variant' => 'Red Medium',
                    'price' => 1000,
                ],
            ],
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
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
        ]);

    $cart->save();

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

    $cart = $cart->fresh();

    expect($cart->lineItems()->count())->toBe(2);
});

test('cant store item with negative quantity', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

    $product->save();

    $data = [
        'product' => $product->id,
        'quantity' => -1,
    ];

    $response = $this
        ->from('/products/'.$product->get('slug'))
        ->post(route('statamic.simple-commerce.cart-items.store'), $data);

    $response->assertRedirect('/products/'.$product->get('slug'));
    $response->assertSessionHasErrors();
});

test('can update item', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
            'slug' => 'food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'quantity' => 2,
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data);

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(2);
});

test('can update item and ensure custom form request is used', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
            'slug' => 'food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        '_request' => encrypt(CartItemUpdateFormRequest::class),
        'quantity' => 2,
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data);

    $response->assertRedirect('/cart');
    $response->assertSessionHasErrors('coolzies');
});

test('cant update item with zero item quantity', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'quantity' => 0,
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data);

    $response->assertSessionHasErrors();

    $cart = $cart->fresh();

    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(1);
});

test('can update item with extra data', function () {
    Config::set('simple-commerce.field_whitelist.line_items', ['gift_note']);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'gift_note' => 'Have a good birthday!',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data);

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    expect(1)->toBe($cart->lineItems()->count());
    expect($cart->lineItems()->first()->metadata()->has('gift_note'))->toBeTrue();
});

test('cant update item with extra data if fields not whitelisted in config', function () {
    Config::set('simple-commerce.field_whitelist.line_items', []);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'gift_note' => 'Have a good birthday!',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->first()->id(),
        ]), $data);

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    expect(1)->toBe($cart->lineItems()->count());
    expect($cart->lineItems()->first()->metadata()->has('gift_note'))->toBeFalse();
});

test('can update item with extra data and ensure existing metadata isnt overwritten', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
                'metadata' => [
                    'foo' => 'bar',
                ],
            ],
        ]);

    $cart->save();

    $data = [
        'bar' => 'baz',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data);

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    expect(1)->toBe($cart->lineItems()->count());
    $this->assertArrayHasKey('metadata', $cart->lineItems()->first());

    $this->assertArrayNotHasKey('foo', $cart->lineItems()->first());
    $this->assertArrayNotHasKey('bar', $cart->lineItems()->first());

    expect('bar')->toBe($cart->lineItems()->toArray()[0]->metadata()->toArray()['foo']);
    expect('baz')->toBe($cart->lineItems()->toArray()[0]->metadata()->toArray()['bar']);
})->skip();

test('can update item with string quantity and ensure quantity is saved as integer', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'quantity' => '3',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data);

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(3);
    expect($cart->lineItems()->toArray()[0]->quantity())->toBeInt();
});

test('can update item and ensure the quantity is not more than stock', function () {
    $product = Product::make()
        ->price(1000)
        ->stock(2)
        ->data([
            'title' => 'Food',
            'slug' => 'food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'quantity' => 5,
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data)
        ->assertSessionHasErrors();

    $cart = $cart->fresh();

    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(1);
});

test('can update item with variant and ensure the quantity is not more than stock', function () {
    $product = Product::make()
        ->data([
            'title' => 'Food',
            'slug' => 'food',
        ])
        ->productVariants([
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
                    'variant' => 'Red Small',
                    'price' => 1000,
                    'stock' => 2,
                ],
            ],
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
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
        ]);

    $cart->save();

    $data = [
        'quantity' => 5,
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data)
        ->assertSessionHasErrors();

    $cart = $cart->fresh();

    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(1);
});

test('cant update item when standard product has no stock', function () {
    $product = Product::make()
        ->price(1000)
        ->stock(0)
        ->data([
            'title' => 'Food',
            'slug' => 'food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'quantity' => 5,
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data)
        ->assertSessionHasErrors();

    $cart = $cart->fresh();

    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(1);
});

test('cant update item when variant product has no stock', function () {
    $product = Product::make()
        ->data([
            'title' => 'Food',
            'slug' => 'food',
        ])
        ->productVariants([
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
                    'variant' => 'Red Small',
                    'price' => 1000,
                    'stock' => 0,
                ],
            ],
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
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
        ]);

    $cart->save();

    $data = [
        'quantity' => 5,
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data)
        ->assertSessionHasErrors();

    $cart = $cart->fresh();

    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(1);
});

test('can update item and request json', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $data = [
        'quantity' => 2,
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->postJson(route('statamic.simple-commerce.cart-items.update', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]), $data);

    $response->assertJsonStructure([
        'status',
        'message',
        'cart',
    ]);

    $cart = $cart->fresh();

    expect($cart->lineItems()->toArray()[0]->quantity())->toBe(2);
});

test('can destroy item', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $cart->save();

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->deleteJson(route('statamic.simple-commerce.cart-items.destroy', [
            'item' => $cart->lineItems()->toArray()[0]->id(),
        ]));

    $response->assertJsonStructure([
        'status',
        'message',
        'cart',
    ]);

    expect($cart->lineItems()->toArray())->toBeEmpty();
})->skip();
