<?php

namespace Tests\Feature\Cart;

use DuncanMcClean\SimpleCommerce\Cart\Calculator\CalculateLineItems;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CanCalculateLineItemsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function tearDown(): void
    {
        CalculateLineItems::priceHook(null);

        parent::tearDown();
    }

    #[Test]
    public function total_can_be_calculated_correctly()
    {
        Collection::make('products')->save();
        $product = tap(Entry::make()->collection('products')->data(['price' => 2550]))->save();

        $cart = Cart::make()->lineItems([
            ['id' => 'a', 'product' => $product->id(), 'quantity' => 2],
        ]);

        (new CalculateLineItems)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(2550, $cart->lineItems()->find('a')->unitPrice());
        $this->assertEquals(5100, $cart->lineItems()->find('a')->total());
    }

    #[Test]
    public function total_can_be_calculated_correctly_when_price_contains_decimal()
    {
        Collection::make('products')->save();
        $product = tap(Entry::make()->collection('products')->data(['price' => '25.50']))->save();

        $cart = Cart::make()->lineItems([
            ['id' => 'a', 'product' => $product->id(), 'quantity' => 2],
        ]);

        (new CalculateLineItems)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(2550, $cart->lineItems()->find('a')->unitPrice());
        $this->assertEquals(5100, $cart->lineItems()->find('a')->total());
    }

    #[Test]
    public function total_can_be_calculated_correctly_with_variant()
    {
        Collection::make('products')->save();

        $product = tap(Entry::make()->collection('products')->data(['product_variants' => [
            'variants' => [['name' => 'Colour', 'values' => ['Red']]],
            'options' => [['key' => 'Red', 'variant' => 'Red', 'price' => 2550]],
        ]]))->save();

        $cart = Cart::make()->lineItems([
            ['id' => 'a', 'product' => $product->id(), 'variant' => 'Red', 'quantity' => 2],
        ]);

        (new CalculateLineItems)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(2550, $cart->lineItems()->find('a')->unitPrice());
        $this->assertEquals(5100, $cart->lineItems()->find('a')->total());
    }

    #[Test]
    public function total_can_be_calculated_correctly_with_variant_when_price_contains_decimal()
    {
        Collection::make('products')->save();

        $product = tap(Entry::make()->collection('products')->data(['product_variants' => [
            'variants' => [['name' => 'Colour', 'values' => ['Red']]],
            'options' => [['key' => 'Red', 'variant' => 'Red', 'price' => '25.50']],
        ]]))->save();

        $cart = Cart::make()->lineItems([
            ['id' => 'a', 'product' => $product->id(), 'variant' => 'Red', 'quantity' => 2],
        ]);

        (new CalculateLineItems)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(2550, $cart->lineItems()->find('a')->unitPrice());
        $this->assertEquals(5100, $cart->lineItems()->find('a')->total());
    }

    #[Test]
    public function total_can_be_calculated_correctly_using_price_hook()
    {
        Collection::make('products')->save();
        $product = tap(Entry::make()->collection('products')->data(['price' => 2550]))->save();

        $cart = Cart::make()->lineItems([
            ['id' => 'a', 'product' => $product->id(), 'quantity' => 2],
        ]);

        CalculateLineItems::priceHook(function ($cart, $lineItem) {
            return 1234;
        });

        (new CalculateLineItems)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(1234, $cart->lineItems()->find('a')->unitPrice());
        $this->assertEquals(2468, $cart->lineItems()->find('a')->total());
    }
}
