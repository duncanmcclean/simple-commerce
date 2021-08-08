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
use Illuminate\Support\Facades\File;

class StandardTaxEngineTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        collect(File::allFiles(base_path('content/simple-commerce/tax-categories')))
            ->each(function ($file) {
                File::delete($file);
            });

        collect(File::allFiles(base_path('content/simple-commerce/tax-rates')))
            ->each(function ($file) {
                File::delete($file);
            });

        collect(File::allFiles(base_path('content/simple-commerce/tax-zones')))
            ->each(function ($file) {
                File::delete($file);
            });
    }

    /** @test */
    public function can_correctly_calculate_tax_rate_based_on_country()
    {
        Config::set('simple-commerce.tax_engine', StandardTaxEngine::class);

        Config::set('simple-commerce.tax_engine_config', [
            'address' => 'billing',
        ]);

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
            'billing_address' => '1 Test Street',
            'billing_country' => 'GB',
        ]);

        $recalculate = $order->recalculate();

        // Ensure tax on line items are right
        $this->assertSame($recalculate->lineItems()->first()['tax'], [
            'amount' => 167,
            'rate' => 20,
            'price_includes_tax' => false,
        ]);

        // Ensure global order tax is right
        $this->assertSame($recalculate->get('tax_total'), 167);
    }

    /** @test */
    public function can_correctly_calculate_tax_rate_based_on_region()
    {
        Config::set('simple-commerce.tax_engine', StandardTaxEngine::class);

        Config::set('simple-commerce.tax_engine_config', [
            'address' => 'billing',
        ]);

        $taxCategory = TaxCategory::make()
            ->id('standard-vat')
            ->name('Standard VAT');

        $taxCategory->save();

        $taxZone = TaxZone::make()
            ->id('uk')
            ->name('United Kingdom')
            ->country('GB')
            ->region('gb-sct');

        $taxZone->save();

        // Just so we can tell this rate apart
        $taxRate = TaxRate::make()
            ->id('scottish-15-vat')
            ->name('15% Scottish VAT')
            ->rate(15)
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
            'billing_address' => '1 Test Street',
            'billing_country' => 'GB',
            'billing_region' => 'gb-sct',
        ]);

        $recalculate = $order->recalculate();

        // Ensure tax on line items are right
        $this->assertSame($recalculate->lineItems()->first()['tax'], [
            'amount' => 130,
            'rate' => 15,
            'price_includes_tax' => false,
        ]);

        // Ensure global order tax is right
        $this->assertSame($recalculate->get('tax_total'), 130);
    }

    /** @test */
    public function can_calculate_tax_rate_when_included_in_price()
    {
        Config::set('simple-commerce.tax_engine', StandardTaxEngine::class);

        Config::set('simple-commerce.tax_engine_config', [
            'address' => 'billing',
        ]);

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
            ->zone($taxZone->id())
            ->includeInPrice(true);

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
            'billing_address' => '1 Test Street',
            'billing_country' => 'GB',
        ]);

        $recalculate = $order->recalculate();

        // Ensure tax on line items are right
        $this->assertSame($recalculate->lineItems()->first()['tax'], [
            'amount' => 167,
            'rate' => 20,
            'price_includes_tax' => true,
        ]);

        // Ensure global order tax is right
        $this->assertSame($recalculate->get('tax_total'), 167);
    }
}
