<?php

use DuncanMcClean\SimpleCommerce\Exceptions\PreventCheckout;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxEngine as StandardTaxEngine;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods\DummyShippingMethod;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Site;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

uses(SetupCollections::class);

beforeEach(function () {
    try {
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
    } catch (DirectoryNotFoundException $e) {
        // That's fine...
    }
});

test('can correctly calculate line item tax rate based on country', function () {
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

    $product = Product::make()
        ->price(1000)
        ->taxCategory($taxCategory->id())
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ])->merge([
        'billing_address' => '1 Test Street',
        'billing_country' => 'GB',
        'use_shipping_address_for_billing' => false,
    ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure tax on line items are right
    $this->assertSame($recalculate->lineItems()->first()->tax(), [
        'amount' => 167,
        'rate' => 20,
        'price_includes_tax' => false,
    ]);

    // Ensure global order tax is right
    expect(167)->toBe($recalculate->taxTotal());
});

test('can correctly calculate line item tax rate based on region', function () {
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

    $product = Product::make()
        ->price(1000)
        ->taxCategory($taxCategory->id())
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ])->merge([
        'billing_address' => '1 Test Street',
        'billing_country' => 'GB',
        'billing_region' => 'gb-sct',
        'use_shipping_address_for_billing' => false,
    ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure tax on line items are right
    $this->assertSame($recalculate->lineItems()->first()->tax(), [
        'amount' => 130,
        'rate' => 15,
        'price_includes_tax' => false,
    ]);

    // Ensure global order tax is right
    expect(130)->toBe($recalculate->taxTotal());
});

test('can correctly calculate line item tax rate when address has region but no addresses have a region', function () {
    Config::set('simple-commerce.tax_engine', StandardTaxEngine::class);

    Config::set('simple-commerce.tax_engine_config', [
        'address' => 'billing',
        'behaviour' => [
            'no_rate_available' => 'prevent_checkout',
        ],
    ]);

    TaxZone::make()->id('everywhere')->save();

    $taxCategory = TaxCategory::make()
        ->id('standard-vat')
        ->name('Standard VAT');

    $taxCategory->save();

    $taxZone = TaxZone::make()
        ->id('uk')
        ->name('United Kingdom')
        ->country('GB');

    $taxZone->save();

    // Just so we can tell this rate apart
    $taxRate = TaxRate::make()
        ->id('scottish-15-vat')
        ->name('15% Scottish VAT')
        ->rate(15)
        ->category($taxCategory->id())
        ->zone($taxZone->id());

    $taxRate->save();

    $product = Product::make()
        ->price(1000)
        ->taxCategory($taxCategory->id())
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ])->merge([
        'billing_address' => '1 Test Street',
        'billing_country' => 'GB',
        'billing_region' => 'gb-sct',
        'use_shipping_address_for_billing' => false,
    ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure tax on line items are right
    $this->assertSame($recalculate->lineItems()->first()->tax(), [
        'amount' => 130,
        'rate' => 15,
        'price_includes_tax' => false,
    ]);

    // Ensure global order tax is right
    expect(130)->toBe($recalculate->taxTotal());
});

test('can correctly calculate line item tax zones when tax rates exists both with and without region', function () {
    Config::set('simple-commerce.tax_engine', StandardTaxEngine::class);

    Config::set('simple-commerce.tax_engine_config', [
        'address' => 'billing',
        'behaviour' => [
            'no_rate_available' => 'prevent_checkout',
        ],
    ]);

    TaxZone::make()->id('everywhere')->save();

    $taxCategory = TaxCategory::make()
        ->id('standard-vat')
        ->name('Standard VAT');

    $taxCategory->save();

    // The first tax rate, the one with no region set.
    $taxZoneA = TaxZone::make()
        ->id('uk')
        ->name('United Kingdom')
        ->country('GB');

    $taxZoneA->save();

    $taxRateA = TaxRate::make()
        ->id('uk-15-vat')
        ->name('20% UK VAT')
        ->rate(15)
        ->category($taxCategory->id())
        ->zone($taxZoneA->id());

    $taxRateA->save();

    // The second tax rate, the one with a region set.
    $taxZoneB = TaxZone::make()
        ->id('uk')
        ->name('United Kingdom')
        ->region('gb-sct')
        ->country('GB');

    $taxZoneB->save();

    $taxRateB = TaxRate::make()
        ->id('scottish-15-vat')
        ->name('15% Scottish VAT')
        ->rate(15)
        ->category($taxCategory->id())
        ->zone($taxZoneB->id());

    $taxRateB->save();

    $product = Product::make()
        ->price(1000)
        ->taxCategory($taxCategory->id())
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ])->merge([
        'billing_address' => '1 Test Street',
        'billing_country' => 'GB',
        'billing_region' => 'gb-sct',
        'use_shipping_address_for_billing' => false,
    ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure tax on line items are right
    $this->assertSame($recalculate->lineItems()->first()->tax(), [
        'amount' => 130,
        'rate' => 15,
        'price_includes_tax' => false,
    ]);

    // Ensure global order tax is right
    expect(130)->toBe($recalculate->taxTotal());
});

test('can calculate line item tax rate when included in price', function () {
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

    $product = Product::make()
        ->price(1000)
        ->taxCategory($taxCategory->id())
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ])->merge([
        'billing_address' => '1 Test Street',
        'billing_country' => 'GB',
        'use_shipping_address_for_billing' => false,
    ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure tax on line items are right
    $this->assertSame($recalculate->lineItems()->first()->tax(), [
        'amount' => 167,
        'rate' => 20,
        'price_includes_tax' => true,
    ]);

    // Ensure global order tax is right
    expect(167)->toBe($recalculate->taxTotal());
});

test('can use default line item tax rate if no rate available', function () {
    Config::set('simple-commerce.tax_engine_config.behaviour.no_rate_available', 'default_rate');

    TaxCategory::make()
        ->id('standard-stuff')
        ->name('Standard Stuff')
        ->save();

    TaxCategory::make()
        ->id('default-category')
        ->name('Default')
        ->save();

    TaxRate::make()
        ->id('default-rate')
        ->name('Default')
        ->rate(12)
        ->includeInPrice(true)
        ->category('default-category')
        ->save();

    $product = Product::make()
        ->price(1000)
        ->taxCategory('standard-stuff')
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ])->merge([
        'billing_address' => '1 Test Street',
        'billing_country' => 'GB',
        'use_shipping_address_for_billing' => false,
    ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure the tax rate is correct on the line item
    expect(12)->toBe($recalculate->lineItems()->first()->tax()['rate']);

    // Ensure global order tax is right
    expect(107)->toBe($recalculate->taxTotal());
});

test('throws prevent checkout exception if no rate available', function () {
    // Ensure an exception is thrown during this test
    $this->expectException(PreventCheckout::class);

    Config::set('simple-commerce.tax_engine_config.behaviour.no_rate_available', 'prevent_checkout');

    TaxCategory::make()
        ->id('standard-stuff')
        ->name('Standard Stuff')
        ->save();

    TaxCategory::make()
        ->id('default-category')
        ->name('Default')
        ->save();

    TaxRate::make()
        ->id('default')
        ->name('Default')
        ->rate(12)
        ->includeInPrice(true)
        ->category('default-category')
        ->save();

    $product = Product::make()
        ->price(1000)
        ->taxCategory('standard-stuff')
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ])->merge([
        'billing_address' => '1 Test Street',
        'billing_country' => 'GB',
        'use_shipping_address_for_billing' => false,
    ]);

    $order->save();

    $order->recalculate();
});

test('uses default address if no address provided', function () {
    Config::set('simple-commerce.tax_engine_config.behaviour.no_address_provided', 'default_address');

    Config::set('simple-commerce.tax_engine_config.behaviour.default_address', [
        'address_line_1' => '',
        'address_line_2' => '',
        'city' => '',
        'region' => '',
        'country' => 'US',
        'zip_code' => '',
    ]);

    TaxCategory::make()
        ->id('standard-stuff')
        ->name('Standard Stuff')
        ->save();

    TaxZone::make()
        ->id('for-the-us')
        ->country('US')
        ->save();

    TaxRate::make()
        ->id('used-for-default-address')
        ->name('used for default address')
        ->rate(99)
        ->includeInPrice(true)
        ->category('standard-stuff')
        ->zone('for-the-us')
        ->save();

    $product = Product::make()
        ->price(1000)
        ->taxCategory('standard-stuff')
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure the tax rate is correct on the line item
    expect(99)->toBe($recalculate->lineItems()->first()->tax()['rate']);

    // Ensure global order tax is right
    expect(497)->toBe($recalculate->taxTotal());
});

test('throws prevent checkout exception if no address provided', function () {
    // Ensure an exception is thrown during this test
    $this->expectException(PreventCheckout::class);

    Config::set('simple-commerce.tax_engine_config.behaviour.no_address_provided', 'prevent_checkout');

    TaxCategory::make()
        ->id('standard-stuff')
        ->name('Standard Stuff')
        ->save();

    TaxZone::make()
        ->id('for-the-us')
        ->country('US')
        ->save();

    TaxRate::make()
        ->id('used-for-default-address')
        ->name('used for default address')
        ->rate(99)
        ->includeInPrice(true)
        ->category('standard-stuff')
        ->zone('for-the-us')
        ->save();

    $product = Product::make()
        ->price(1000)
        ->taxCategory('standard-stuff')
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => app('stache')->generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure the tax rate is correct on the line item
    expect(99)->toBe($recalculate->lineItems()->first()->tax()['rate']);

    // Ensure global order tax is right
    expect(497)->toBe($recalculate->taxTotal());
});

// https://github.com/duncanmcclean/simple-commerce/issues/856
test('can use default shipping tax rate if no rate available', function () {
    Config::set('simple-commerce.tax_engine_config.behaviour.no_rate_available', 'default_rate');
    SimpleCommerce::registerShippingMethod(Site::current()->handle(), DummyShippingMethod::class);

    TaxZone::make()
        ->id('everywhere')
        ->name('Everywhere')
        ->save();

    TaxCategory::make()
        ->id('no-tax')
        ->name('No TAX')
        ->save();

    TaxRate::make()
        ->id('no-tax')
        ->name('No Tax')
        ->rate(0)
        ->includeInPrice(true)
        ->category('no-tax')
        ->zone('everywhere')
        ->save();

    TaxCategory::make()
        ->id('shipping')
        ->name('Shipping')
        ->save();

    TaxRate::make()
        ->id('shipping-rate')
        ->name('Default = Shipping')
        ->rate(10)
        ->includeInPrice(true)
        ->category('shipping')
        ->zone('everywhere')
        ->save();

    $product = Product::make()
        ->price(1000)
        ->taxCategory('no-tax')
        ->data([
            'title' => 'Cat Food',
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => app('stache')->generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ])
        ->merge([
            'billing_address' => '1 Test Street',
            'billing_country' => 'GB',
            'use_shipping_address_for_billing' => false,
            'shipping_method' => DummyShippingMethod::handle(),
        ]);

    $order->save();

    $recalculate = $order->recalculate();

    // Ensure 10% is deducted from shipping_total (500 -> 450)
    expect($recalculate->shippingTotal())->toBe(455);

    // Ensure tax total is 50
    expect($recalculate->taxTotal())->toBe(45);
});
