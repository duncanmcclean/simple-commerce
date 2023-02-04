<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Tags\CartTags;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;
use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Statamic;

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
    public function can_get_cart_items_total_with_cart_tax_total()
    {
        $cart = Order::make()->itemsTotal(2550)->taxTotal(620);
        $cart->save();

        $this->fakeCart($cart);

        $this->assertSame('£31.70', (string) $this->tag('{{ sc:cart:itemsTotalWithTax }}'));
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

    /**
     * @test
     * https://github.com/duncanmcclean/simple-commerce/pull/759
     */
    public function can_get_cart_tax_total_split()
    {
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

        $this->fakeCart($cart);

        // Get tax sum for products with default tax rate
        $cartProduct1 = $cart->lineItems()->slice(0, 1)->first();
        $cartProduct2 = $cart->lineItems()->slice(1, 1)->first();

        $taxDefault = $cartProduct1->tax()['amount'] + $cartProduct2->tax()['amount'];
        $taxDefaultFormatted = Currency::parse($taxDefault, Site::default());

        // Get tax sum for products with reduced tax rate
        $cartProduct3 = $cart->lineItems()->slice(2, 1)->first();

        $taxReduced = $cartProduct3->tax()['amount'];
        $taxReducedFormatted = Currency::parse($taxReduced, Site::default());

        // Expected tag output format = '7:£12.34|19:£56.78'
        $renderedTag = $this->tag('{{ sc:cart:taxTotalSplit }}{{ rate }}:{{ amount }}|{{ /sc:cart:taxTotalSplit }}');

        $this->assertStringContainsString($taxRateDefault->rate() . ':' . $taxDefaultFormatted, $renderedTag);
        $this->assertStringContainsString($taxRateReduced->rate() . ':' . $taxReducedFormatted, $renderedTag);
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
    public function can_fetch_add_item_form_data()
    {
        $form = Statamic::tag('sc:cart:addItem')->fetch();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $form['params_html']);
        $this->assertEquals($form['attrs_html'], 'method="POST" action="http://localhost/!/simple-commerce/cart-items"');

        $this->assertArrayHasKey('_token', $form['params']);
        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/simple-commerce/cart-items');
        $this->assertEquals($form['attrs']['method'], 'POST');
    }

    /**
     * @test
     * https://github.com/duncanmcclean/simple-commerce/issues/756
     */
    public function can_output_add_item_form_and_ensure_external_redirect_urls_are_correct()
    {
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

            <input type="hidden" name="product" value="{{ ' . $product->id . ' }}">
            <input type="number" name="quantity">
            <button type="submit">Add to cart</submit>
        ');

        $usage = $this->tag->addItem();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="https://duncanmcclean.com"', $usage);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="https://statamic.dev/installing"', $usage);
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
    public function can_fetch_update_item_form_data()
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

        $form = Statamic::tag('sc:cart:updateItem')->params([
            'product' => $product->id,
        ])->fetch();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $form['params_html']);
        $this->assertEquals($form['attrs_html'], 'method="POST" action="http://localhost/!/simple-commerce/cart-items/'.$lineItem->id.'"');

        $this->assertArrayHasKey('_token', $form['params']);
        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/simple-commerce/cart-items/'.$lineItem->id.'');
        $this->assertEquals($form['attrs']['method'], 'POST');
    }

    /**
     * @test
     * https://github.com/duncanmcclean/simple-commerce/pull/792#issuecomment-1413598741
     */
    public function can_output_update_item_form_and_ensure_the_the_item_parameter_isnt_being_returned_as_an_attribute_on_the_form_tag()
    {
        $this->tag->setParameters([
            'item' => 'absolute-load-of-jiberish',
        ]);

        $this->tag->setContent('
            <h2>Update Item</h2>

            <input type="number" name="quantity">
            <button type="submit">Update item in cart</button>
        ');

        $usage = $this->tag->updateItem();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart-items/absolute-load-of-jiberish"', $usage);

        $this->assertStringNotContainsString('item="absolute-load-of-jiberish"', $usage);
    }

    /** @test */
    public function can_output_remove_item_form()
    {
        $this->tag->setParameters([
            'item' => 'smelly-cat',
        ]);

        $this->tag->setContent('
            <h2>Remove item from cart?</h2>

            <button type="submit">Update item in cart</button>
        ');

        $usage = $this->tag->removeItem();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $usage);
        $this->assertStringContainsString('method="POST" action="http://localhost/!/simple-commerce/cart-items/smelly-cat"', $usage);
    }

    /**
     * @test
     * https://github.com/duncanmcclean/simple-commerce/pull/792#issuecomment-1413598741
     */
    public function can_output_remove_item_form_and_ensure_the_item_parameter_isnt_being_returned_as_an_attribute_on_the_form_tag()
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

        $this->assertStringNotContainsString('item="smelly-cat"', $usage);
    }

    /** @test */
    public function can_fetch_remove_item_form_data()
    {
        $form = Statamic::tag('sc:cart:removeItem')->params([
            'item' => 'smelly-cat',
        ])->fetch();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $form['params_html']);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE"', $form['params_html']);
        $this->assertEquals($form['attrs_html'], 'method="POST" action="http://localhost/!/simple-commerce/cart-items/smelly-cat"');

        $this->assertArrayHasKey('_token', $form['params']);
        $this->assertEquals($form['params']['_method'], 'DELETE');
        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/simple-commerce/cart-items/smelly-cat');
        $this->assertEquals($form['attrs']['method'], 'POST');
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
    public function can_fetch_cart_update_form_data()
    {
        $form = Statamic::tag('sc:cart:update')->fetch();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $form['params_html']);
        $this->assertEquals($form['attrs_html'], 'method="POST" action="http://localhost/!/simple-commerce/cart"');

        $this->assertArrayHasKey('_token', $form['params']);
        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/simple-commerce/cart');
        $this->assertEquals($form['attrs']['method'], 'POST');
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
    public function can_fetch_cart_empty_form_data()
    {
        $form = Statamic::tag('sc:cart:empty')->fetch();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $form['params_html']);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE"', $form['params_html']);
        $this->assertEquals($form['attrs_html'], 'method="POST" action="http://localhost/!/simple-commerce/cart"');

        $this->assertArrayHasKey('_token', $form['params']);
        $this->assertEquals($form['params']['_method'], 'DELETE');
        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/simple-commerce/cart');
        $this->assertEquals($form['attrs']['method'], 'POST');
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

        $this->assertTrue($usage instanceof \Statamic\Fields\Value || is_string($usage));
        $this->assertSame($usage instanceof \Statamic\Fields\Value ? $usage->value() : $usage, 'Deliver by front door.');
    }

    /**
     * @test
     * https://github.com/duncanmcclean/simple-commerce/pull/650
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

        $this->assertTrue($usage instanceof \Statamic\Fields\Value || is_int($usage));
        $this->assertSame($usage instanceof \Statamic\Fields\Value ? $usage->value() : $usage, 1590);
    }

    /** @test */
    public function cant_get_data_from_cart_if_there_is_no_cart()
    {
        $this->session(['simple-commerce-cart' => null]);
        $this->tag->setParameters([]);

        $usage = $this->tag->wildcard('note');

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
