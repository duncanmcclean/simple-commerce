<?php

use DuncanMcClean\SimpleCommerce\Currency;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Tags\CartTags;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Statamic;

uses(SetupCollections::class);

beforeEach(function () {
    $this->setupCollections();

    Collection::find('orders')->queryEntries()->get()->each->delete();

    $this->tag = resolve(CartTags::class)
        ->setParser(Antlers::parser())
        ->setContext([]);
});

test('can get index', function () {
    fakeCart();

    expect((string) tag('{{ sc:cart }}{{ note }}{{ /sc:cart }}'))->toBe('Special note.');
    expect((string) tag('{{ sc:cart }}{{ if {is_paid} }}true{{ else }}false{{ /if }}{{ /sc:cart }}'))->toBe('false');
});

test('user has a cart if cart does not exist', function () {
    expect((string) tag('{{ if sc:cart:has }}Has cart{{ else }}No cart{{ /if }}'))->toBe('No cart');
});

test('user has a cart if cart exists', function () {
    fakeCart();

    expect((string) tag('{{ if {sc:cart:has} }}Has cart{{ else }}No cart{{ /if }}'))->toBe('Has cart');
});

test('can get line items', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
        ]);

    $product->save();

    $cart = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 5,
            'total' => 1000,
        ],
    ]);

    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:items }}{{ quantity }}{{ /sc:cart:items }}'))->toContain('5');
});

test('can get line items count', function () {
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
    ]);

    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:count }}'))->toBe('2');
});

test('can get line items quantity total', function () {
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
            'id' => Stache::generateId(),
            'product' => $productOne->id,
            'quantity' => 7,
            'total' => 1000,
        ],
        [
            'id' => Stache::generateId(),
            'product' => $productTwo->id,
            'quantity' => 4,
            'total' => 1200,
        ],
    ]);

    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:quantityTotal }}'))->toBe('11');
});

test('can get cart total', function () {
    $cart = Order::make()->grandTotal(2550);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:total }}'))->toBe('£25.50');
});

test('can get cart free status if order is free', function () {
    $cart = Order::make()->grandTotal(0);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ if {sc:cart:free} }}Yes{{ else }}No{{ /if }}'))->toBe('Yes');
});

test('can get cart free status if order is paid', function () {
    $cart = Order::make()->grandTotal(2550);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ if {sc:cart:free} }}Yes{{ else }}No{{ /if }}'))->toBe('No');
});

test('can get cart grand total', function () {
    $cart = Order::make()->grandTotal(2550);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:grandTotal }}'))->toBe('£25.50');
});

test('can get line items total', function () {
    $cart = Order::make()->itemsTotal(2550);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:itemsTotal }}'))->toBe('£25.50');
});

test('can get line items total with cart tax total, when tax is included in the price', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => 'blah',
                'product' => $product->id(),
                'quantity' => 1,
                'total' => 800,
                'tax' => [
                    'amount' => 200,
                    'rate' => 20,
                    'price_includes_tax' => true,
                ],
            ],
        ])
        ->itemsTotal(1000)
        ->taxTotal(200)
        ->grandTotal(1000);

    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:itemsTotalWithTax }}'))->toBe('£10.00');
});

test('can get line items total with cart tax total, when tax is not included in the price', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
        ]);

    $product->save();

    $cart = Order::make()
        ->lineItems([
            [
                'id' => 'blah',
                'product' => $product->id(),
                'quantity' => 1,
                'total' => 1000,
                'tax' => [
                    'amount' => 200,
                    'rate' => 20,
                    'price_includes_tax' => false,
                ],
            ],
        ])
        ->itemsTotal(1000)
        ->taxTotal(200)
        ->grandTotal(1200);

    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:itemsTotalWithTax }}'))->toBe('£12.00');
});

test('can get cart shipping total', function () {
    $cart = Order::make()->shippingTotal(2550);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:shippingTotal }}'))->toBe('£25.50');
});

test('can get cart shipping total with tax when tax is included in the price', function () {
    $cart = Order::make()->shippingTotal(826)->merge([
        'shipping_tax' => [
            'amount' => 174,
            'rate' => 21,
            'price_includes_tax' => true,
        ],
    ]);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:shippingTotalWithTax }}'))->toBe('£10.00');
});

test('can get cart shipping total with tax when tax is not included in the price', function () {
    $cart = Order::make()->shippingTotal(2000)->merge([
        'shipping_tax' => [
            'amount' => 400,
            'rate' => 20,
            'price_includes_tax' => false,
        ],
    ]);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:shippingTotalWithTax }}'))->toBe('£24.00');
});

test('can get cart tax total', function () {
    $cart = Order::make()->taxTotal(2550);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:taxTotal }}'))->toBe('£25.50');
});

/**
 * https://github.com/duncanmcclean/simple-commerce/pull/759
 */
test('can get cart tax total split', function () {
    Config::set('simple-commerce.tax_engine', StandardTaxEngine::class);

    Config::set('simple-commerce.tax_engine_config', [
        'address' => 'billing',
    ]);

    $taxZone = TaxZone::make()
        ->id('uk')
        ->name('United Kingdom')
        ->country('GB');

    $taxZone->save();

    // Create default tax
    $taxCategoryDefault = TaxCategory::make()
        ->id('default-vat')
        ->name('Default VAT');

    $taxCategoryDefault->save();

    $taxRateDefault = TaxRate::make()
        ->id('default-vat')
        ->name('19% VAT')
        ->rate(19)
        ->category($taxCategoryDefault->id())
        ->zone($taxZone->id());

    $taxRateDefault->save();

    // Create reduced tax
    $taxCategoryReduced = TaxCategory::make()
        ->id('reduced-vat')
        ->name('Reduced VAT');

    $taxCategoryReduced->save();

    $taxRateReduced = TaxRate::make()
        ->id('reduced-vat')
        ->name('7% VAT')
        ->rate(7)
        ->category($taxCategoryReduced->id())
        ->zone($taxZone->id());

    $taxRateReduced->save();

    // Create test products
    $productOne = Product::make()
        ->price(799)
        ->taxCategory($taxCategoryDefault->id())
        ->data([
            'title' => 'Cat Food',
        ]);

    $productOne->save();

    $productTwo = Product::make()
        ->price(1234)
        ->taxCategory($taxCategoryDefault->id())
        ->data([
            'title' => 'Dog Food',
        ]);

    $productTwo->save();

    $productThree = Product::make()
        ->price(45699)
        ->taxCategory($taxCategoryReduced->id())
        ->data([
            'title' => 'Elephant Food',
        ]);

    $productThree->save();

    // Create face cart
    $cart = Order::make()
        ->lineItems([
            [
                'id' => app('stache')->generateId(),
                'product' => $productOne->id,
                'quantity' => 4,
            ],
            [
                'id' => app('stache')->generateId(),
                'product' => $productTwo->id,
                'quantity' => 7,
            ],
            [
                'id' => app('stache')->generateId(),
                'product' => $productThree->id,
                'quantity' => 3,
            ],
        ])
        ->merge([
            'billing_address' => '1 Test Street',
            'billing_country' => 'GB',
            'use_shipping_address_for_billing' => false,
        ]);

    $cart->save();
    $cart->recalculate();

    fakeCart($cart);

    // Get tax sum for products with default tax rate
    $cartProduct1 = $cart->lineItems()->slice(0, 1)->first();
    $cartProduct2 = $cart->lineItems()->slice(1, 1)->first();

    $taxDefault = $cartProduct1->tax()['amount'] + $cartProduct2->tax()['amount'];
    $taxDefaultFormatted = Currency::parse($taxDefault, Site::default());

    // Get tax sum for products with reduced tax rate
    $cartProduct3 = $cart->lineItems()->slice(2, 1)->first();

    $taxReduced = $cartProduct3->tax()['amount'];
    $taxReducedFormatted = Currency::parse($taxReduced, Site::default());

    // Expected tag output format = '19:1234|7:5678'
    $renderedTag = (string) tag('{{ sc:cart:rawTaxTotalSplit }}{{ rate }}:{{ amount }}|{{ /sc:cart:rawTaxTotalSplit }}');

    expect($renderedTag)->toContain($taxRateDefault->rate().':'.$taxDefault);
    expect($renderedTag)->toContain($taxRateReduced->rate().':'.$taxReduced);

    // Expected tag output format = '7:£12.34|19:£56.78'
    $renderedTag = (string) tag('{{ sc:cart:taxTotalSplit }}{{ rate }}:{{ amount }}|{{ /sc:cart:taxTotalSplit }}');

    expect($renderedTag)->toContain($taxRateDefault->rate().':'.$taxDefaultFormatted);
    expect($renderedTag)->toContain($taxRateReduced->rate().':'.$taxReducedFormatted);
});

test('can get cart coupon total', function () {
    $cart = Order::make()->couponTotal(2550);
    $cart->save();

    fakeCart($cart);

    expect((string) tag('{{ sc:cart:couponTotal }}'))->toBe('£25.50');
});

test('can output add item form', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
        ]);

    $product->save();

    $this->tag->setParameters([]);

    $this->tag->setContent('
        <h2>Add Item</h2>

        <input type="hidden" name="product" value="'.$product->id.'">
        <input type="number" name="quantity">
        <button type="submit">Add to cart</button>
    ');

    $usage = $this->tag->addItem();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart-items"');
});

test('can fetch add item form data', function () {
    $form = Statamic::tag('sc:cart:addItem')->fetch();

    expect($form['params_html'])->toContain('<input type="hidden" name="_token"');
    expect('method="POST" action="http://localhost/!/simple-commerce/cart-items"')->toEqual($form['attrs_html']);

    $this->assertArrayHasKey('_token', $form['params']);
    expect('http://localhost/!/simple-commerce/cart-items')->toEqual($form['attrs']['action']);
    expect('POST')->toEqual($form['attrs']['method']);
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/756
 */
test('can output add item form and ensure external redirect urls are correct', function () {
    Config::set('simple-commerce.disable_form_parameter_validation', true);

    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
        ]);

    $product->save();

    $this->tag->setParameters([
        'redirect' => 'https://duncanmcclean.com',
        'error_redirect' => 'https://statamic.dev/installing',
    ]);

    $this->tag->setContent('
        <h2>Add Item</h2>

        <input type="hidden" name="product" value="'.$product->id.'">
        <input type="number" name="quantity">
        <button type="submit">Add to cart</button>
    ');

    $usage = $this->tag->addItem();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('<input type="hidden" name="_redirect" value="https://duncanmcclean.com"');
    expect($usage)->toContain('<input type="hidden" name="_error_redirect" value="https://statamic.dev/installing"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart-items"');
});

test('can output update item form', function () {
    $this->tag->setParameters([
        'item' => 'absolute-load-of-jiberish',
    ]);

    $this->tag->setContent('
        <h2>Update Item</h2>

        <input type="number" name="quantity">
        <button type="submit">Update item in cart</button>
    ');

    $usage = $this->tag->updateItem();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart-items/absolute-load-of-jiberish"');
});

test('can output update item form with product parameter', function () {
    $cart = fakeCart();

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
        <button type="submit">Update item in cart</button>
    ');

    $usage = $this->tag->updateItem();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart-items/'.$lineItem->id.'"');
    expect($usage)->toContain('Product: '.$product->id);
});

test('can fetch update item form data', function () {
    $cart = fakeCart();

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

    $form = Statamic::tag('sc:cart:updateItem')->params([
        'product' => $product->id,
    ])->fetch();

    expect($form['params_html'])->toContain('<input type="hidden" name="_token"');
    $this->assertEquals($form['attrs_html'], 'method="POST" action="http://localhost/!/simple-commerce/cart-items/'.$lineItem->id.'"');

    $this->assertArrayHasKey('_token', $form['params']);
    $this->assertEquals($form['attrs']['action'], 'http://localhost/!/simple-commerce/cart-items/'.$lineItem->id.'');
    expect('POST')->toEqual($form['attrs']['method']);
});

/**
 * https://github.com/duncanmcclean/simple-commerce/pull/792#issuecomment-1413598741
 */
test('can output update item form and ensure the the item parameter isnt being returned as an attribute on the form tag', function () {
    $this->tag->setParameters([
        'item' => 'absolute-load-of-jiberish',
    ]);

    $this->tag->setContent('
        <h2>Update Item</h2>

        <input type="number" name="quantity">
        <button type="submit">Update item in cart</button>
    ');

    $usage = $this->tag->updateItem();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart-items/absolute-load-of-jiberish"');

    $this->assertStringNotContainsString('item="absolute-load-of-jiberish"', $usage);
});

test('can output remove item form', function () {
    $this->tag->setParameters([
        'item' => 'smelly-cat',
    ]);

    $this->tag->setContent('
        <h2>Remove item from cart?</h2>

        <button type="submit">Update item in cart</button>
    ');

    $usage = $this->tag->removeItem();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart-items/smelly-cat"');
});

/**
 * https://github.com/duncanmcclean/simple-commerce/pull/792#issuecomment-1413598741
 */
test('can output remove item form and ensure the item parameter isnt being returned as an attribute on the form tag', function () {
    $this->tag->setParameters([
        'item' => 'smelly-cat',
    ]);

    $this->tag->setContent('
        <h2>Remove item from cart?</h2>

        <button type="submit">Update item in cart</submit>
    ');

    $usage = $this->tag->removeItem();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart-items/smelly-cat"');

    $this->assertStringNotContainsString('item="smelly-cat"', $usage);
});

test('can fetch remove item form data', function () {
    $form = Statamic::tag('sc:cart:removeItem')->params([
        'item' => 'smelly-cat',
    ])->fetch();

    expect($form['params_html'])->toContain('<input type="hidden" name="_token"');
    expect($form['params_html'])->toContain('<input type="hidden" name="_method" value="DELETE"');
    expect('method="POST" action="http://localhost/!/simple-commerce/cart-items/smelly-cat"')->toEqual($form['attrs_html']);

    $this->assertArrayHasKey('_token', $form['params']);
    expect('DELETE')->toEqual($form['params']['_method']);
    expect('http://localhost/!/simple-commerce/cart-items/smelly-cat')->toEqual($form['attrs']['action']);
    expect('POST')->toEqual($form['attrs']['method']);
});

test('can output remove item form with product parameter', function () {
    $cart = fakeCart();

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

        <button type="submit">Update item in cart</button>
    ');

    $usage = $this->tag->removeItem();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart-items/'.$lineItem->id.'"');
    expect($usage)->toContain('Product: '.$product->id);
});

test('can output cart update form', function () {
    $cart = Order::make()->merge([]);

    $cart->save();

    fakeCart($cart);

    $this->tag->setParameters([]);

    $this->tag->setContent('
        <h2>Update cart</h2>

        <input name="name">
        <input name="email">

        <button type="submit">Update cart</button>
    ');

    $usage = $this->tag->update();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart"');
});

test('can fetch cart update form data', function () {
    $form = Statamic::tag('sc:cart:update')->fetch();

    expect($form['params_html'])->toContain('<input type="hidden" name="_token"');
    expect('method="POST" action="http://localhost/!/simple-commerce/cart"')->toEqual($form['attrs_html']);

    $this->assertArrayHasKey('_token', $form['params']);
    expect('http://localhost/!/simple-commerce/cart')->toEqual($form['attrs']['action']);
    expect('POST')->toEqual($form['attrs']['method']);
});

test('can output cart empty form', function () {
    $this->tag->setParameters([]);

    $this->tag->setContent('
        <h2>Empty cart?</h2>

        <button type="submit">Empty</button>
    ');

    $usage = $this->tag->empty();

    expect($usage)->toContain('<input type="hidden" name="_token"');
    expect($usage)->toContain('method="POST" action="http://localhost/!/simple-commerce/cart"');
});

test('can fetch cart empty form data', function () {
    $form = Statamic::tag('sc:cart:empty')->fetch();

    expect($form['params_html'])->toContain('<input type="hidden" name="_token"');
    expect($form['params_html'])->toContain('<input type="hidden" name="_method" value="DELETE"');
    expect('method="POST" action="http://localhost/!/simple-commerce/cart"')->toEqual($form['attrs_html']);

    $this->assertArrayHasKey('_token', $form['params']);
    expect('DELETE')->toEqual($form['params']['_method']);
    expect('http://localhost/!/simple-commerce/cart')->toEqual($form['attrs']['action']);
    expect('POST')->toEqual($form['attrs']['method']);
});

test('can output if product already exists in cart', function () {
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

    fakeCart($cart);

    $this->tag->setParameters([
        'product' => $product->id,
    ]);

    $usage = $this->tag->alreadyExists();

    expect($usage)->toBeTrue();
});

test('can output if product and variant already exists in cart', function () {
    $product = Product::make()
        ->data([
            'title' => 'Dog Food',
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
                    'price' => 5000,
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

    fakeCart($cart);

    $this->tag->setParameters([
        'product' => $product->id,
        'variant' => 'Red_Small',
    ]);

    $usage = $this->tag->alreadyExists();

    expect($usage)->toBeTrue();
});

test('can output if product does not already exists in cart', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Dog Food',
        ]);

    $product->save();

    $cart = Order::make()->merge([]);
    $cart->save();

    fakeCart($cart);

    $this->tag->setParameters([
        'product' => $product->id,
    ]);

    $usage = $this->tag->alreadyExists();

    expect($usage)->toBeFalse();
});

test('cant output if product does not already exist in cart because there is no cart', function () {
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

    expect($usage)->toBeFalse();
});

test('can get data from cart', function () {
    $cart = Order::make()->merge([
        'title' => '#0001',
        'note' => 'Deliver by front door.',
    ]);

    $cart->save();

    $this->session(['simple-commerce-cart' => $cart->id]);
    $this->tag->setParameters([]);

    $usage = $this->tag->wildcard('note');

    expect($usage instanceof \Statamic\Fields\Value || is_string($usage))->toBeTrue();
    expect('Deliver by front door.')->toBe($usage instanceof \Statamic\Fields\Value ? $usage->value() : $usage);
});

/**
 * https://github.com/duncanmcclean/simple-commerce/pull/650
 */
test('can get data from cart when method should be converted to studly case', function () {
    $cart = Order::make()->merge([
        'title' => '#0001',
        'note' => 'Deliver by front door.',
    ])->grandTotal(1590);

    $cart->save();

    $this->session(['simple-commerce-cart' => $cart->id]);
    $this->tag->setParameters([]);

    $usage = $this->tag->wildcard('raw_grand_total');

    expect($usage instanceof \Statamic\Fields\Value || is_int($usage))->toBeTrue();
    expect(1590)->toBe($usage instanceof \Statamic\Fields\Value ? $usage->value() : $usage);
});

test('cant get data from cart if there is no cart', function () {
    $this->session(['simple-commerce-cart' => null]);
    $this->tag->setParameters([]);

    $usage = $this->tag->wildcard('note');

    expect($usage instanceof \Statamic\Fields\Value || is_string($usage))->toBeFalse();
    expect(null)->toBe($usage instanceof \Statamic\Fields\Value ? $usage->value() : $usage);
});
