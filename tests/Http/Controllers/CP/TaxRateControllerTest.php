<?php

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    collect(File::allFiles(base_path('content/simple-commerce/tax-rates')))
        ->each(function ($file) {
            File::delete($file);
        });
});

test('can get index', function () {
    TaxZone::make()->id('the-uk')->name('The UK')->country('GB')->save();
    TaxCategory::make()->id('standard')->name('Standard Products')->description('For all the standard products')->save();

    TaxRate::make()
        ->name('UK - Standard Products')
        ->rate(20)
        ->zone('the-uk')
        ->category('standard')
        ->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/rates')
        ->assertOk()
        ->assertSee('UK - Standard Products')
        ->assertSee('20%')
        ->assertSee('The UK');
});

test('can create tax rate', function () {
    TaxCategory::make()->id('standard')->name('Standard Products')->description('For all the standard products')->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/rates/create?taxCategory=standard')
        ->assertOk()
        ->assertSee('Create Tax Rate')
        ->assertSee('Name')
        ->assertSee('Rate')
        ->assertSee('Tax Zone')
        ->assertSee('Prices include tax?');
});

test('can store tax rate', function () {
    TaxZone::make()->id('the-uk')->name('The UK')->country('GB')->save();
    TaxCategory::make()->id('special')->name('Special Products')->description('For all the special products')->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/rates/create', [
            'name' => 'UK - Special',
            'rate' => 5,
            'category' => 'special',
            'zone' => 'the-uk',
            'include_in_price' => 'true',
        ])
        ->assertRedirect();
});

test('can edit tax rate', function () {
    TaxZone::make()->id('the-uk')->name('The UK')->country('GB')->save();
    TaxCategory::make()->id('standard')->name('Standard Products')->description('For all the standard products')->save();

    TaxRate::make()
        ->id('uk-standard-products')
        ->name('UK - Standard Products')
        ->rate(20)
        ->zone('the-uk')
        ->category('standard')
        ->includeInPrice(true)
        ->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/rates/uk-standard-products/edit')
        ->assertOk()
        ->assertSee('UK - Standard Products')
        ->assertSee('20')
        ->assertSee('the-uk')
        ->assertSee('standard')
        ->assertSee('name="include_in_price" value="true"', false);
});

test('can update tax rate', function () {
    TaxZone::make()->id('the-uk')->name('The UK')->country('GB')->save();
    TaxCategory::make()->id('standard')->name('Standard Products')->description('For all the standard products')->save();

    TaxRate::make()
        ->id('uk-standard-products')
        ->name('UK - Standard Products')
        ->rate(20)
        ->zone('the-uk')
        ->category('standard')
        ->includeInPrice(false)
        ->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/rates/uk-standard-products/edit', [
            'name' => 'UK - Standard Products (15% for COVID)',
            'rate' => 15,
            'zone' => 'the-uk',
            'category' => 'standard',
            'include_in_price' => 'true',
        ])
        ->assertRedirect('/cp/simple-commerce/tax/rates/uk-standard-products/edit');
});

test('can destroy tax rate', function () {
    TaxZone::make()->id('the-uk')->name('The UK')->country('GB')->save();
    TaxCategory::make()->id('standard')->name('Standard Products')->description('For all the standard products')->save();

    TaxRate::make()
        ->id('uk-standard-products')
        ->name('UK - Standard Products')
        ->rate(20)
        ->zone('the-uk')
        ->category('standard')
        ->save();

    $this
        ->actingAs(user())
        ->delete('/cp/simple-commerce/tax/rates/uk-standard-products/delete')
        ->assertJson(['success' => true]);
});
