<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tax;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxEngine as StandardTaxEngine;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class StandardTaxEngineTest extends TestCase
{
    /** @test */
    public function can_correctly_calculate_tax_rate_based_on_country()
    {
        Config::set('simple-commerce.tax_engine', StandardTaxEngine::class);

        $taxCategory = TaxCategory::make()
            ->id('standard-vat')
            ->name('Standard VAT');

        $taxCategory->save();

        $taxZone = TaxZone::make()
            ->id('uk')
            ->name('United Kingdom')
            ->country('GB');

        $taxZone->save();

        $taxRate = TaxRate::make()
            ->id('uk-20-vat')
            ->name('20% VAT')
            ->rate(20)
            ->category($taxCategory->id())
            ->zone($taxZone->id());

        $taxRate->save();

        $product = Product::create([
            'title' => 'Cat Food',
            'price' => 1000,
            'tax_category' => $taxCategory->id(),
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id' => app('stache')->generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
        ]);

        $recalculate = $order->recalculate();

        // Assuming we're including tax in prices
        $this->assertSame()

        dd($recalculate);

        // category

        // zone

        // rate

        // product

        // order

        // calculate tax

        // assert rate is correct
    }

    /** @test */
    public function can_correctly_calculate_tax_rate_based_on_region()
    {
        //
    }
}
