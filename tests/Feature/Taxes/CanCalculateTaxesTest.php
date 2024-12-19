<?php

namespace Feature\Taxes;

use DuncanMcClean\SimpleCommerce\Cart\Calculator\CalculateTaxes;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\TaxClass;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CanCalculateTaxesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();

        TaxClass::make()->handle('standard')->save();

        $path = base_path('content/simple-commerce/tax-zones.yaml');

        File::delete($path);
        File::ensureDirectoryExists(Str::beforeLast($path, '/'));
    }

    // todo: tax calculations for line items with quantities
    // todo: multiple tax line items - tax total should be the sum of all tax line items
    // todo: multiple tax rates for a single line item
    // todo: doesn't calculate taxes when no tax zone is set / no tax rates are available

    #[Test]
    public function calculates_line_item_tax_when_price_includes_tax()
    {
        config(['statamic.simple-commerce.taxes.price_includes_tax' => true]);

        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'TODO', 'zone' => 'TODO', 'amount' => 1667],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(1667, $lineItem->taxTotal());
        $this->assertEquals(10000, $lineItem->total());
        $this->assertEquals(1667, $cart->taxTotal());
    }

    #[Test]
    public function calculates_line_item_tax_when_price_excludes_tax()
    {
        config(['statamic.simple-commerce.taxes.price_includes_tax' => false]);

        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'TODO', 'zone' => 'TODO', 'amount' => 2000],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(2000, $lineItem->taxTotal());
        $this->assertEquals(12000, $lineItem->total());
        $this->assertEquals(2000, $cart->taxTotal());
    }

    #[Test]
    public function calculates_line_item_tax_when_discount_is_applied()
    {
        $this->markTestIncomplete();
    }

    // todo: shipping
}