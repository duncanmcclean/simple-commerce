<?php

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    collect(File::allFiles(base_path('content/simple-commerce/tax-zones')))
        ->each(function ($file) {
            File::delete($file);
        });
});

test('can get index', function () {
    TaxZone::make()
        ->name('United Kingdom (apart from the fact Scotland wants to leave)')
        ->country('GB')
        ->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/zones')
        ->assertOk()
        ->assertSee('United Kingdom (apart from the fact')
        ->assertSee('United Kingdom');
});

test('can create tax zone', function () {
    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/zones/create')
        ->assertOk()
        ->assertSee('Create Tax Zone')
        ->assertSee('Name')
        ->assertSee('Country')
        ->assertSee('region-selector');
});

test('can store tax zone', function () {
    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/zones/create', [
            'name' => 'Special Products',
            'country' => 'DE',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

test('cant store tax zone when there is already a tax zone covering the same country', function () {
    TaxZone::make()
        ->id('the-us')
        ->name('The US')
        ->country('US')
        ->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/zones/create', [
            'name' => 'United States',
            'country' => 'US',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();
});

test('cant store tax zone when there is already a tax zone covering the same country and region', function () {
    TaxZone::make()
        ->id('the-us')
        ->name('The Alaska State')
        ->country('US')
        ->region('us-ak')
        ->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/zones/create', [
            'name' => 'Alaska',
            'country' => 'US',
            'region' => 'us-ak',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();
});

test('can edit tax zone', function () {
    TaxZone::make()
        ->id('united-kingdom')
        ->name('United Kingdom (apart from the fact Scotland wants to leave)')
        ->country('GB')
        ->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/zones/united-kingdom/edit')
        ->assertOk()
        ->assertSee('United Kingdom (apart from the fact')
        ->assertSee('United Kingdom');
});

test('can update tax zone', function () {
    TaxZone::make()
        ->id('united-kingdom')
        ->name('United Kingdom (apart from the fact Scotland wants to leave)')
        ->country('GB')
        ->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/zones/united-kingdom/edit', [
            'name' => 'The United Kingdom of Scotland, Northern Ireland, Wales and England',
            'country' => 'GB',
        ])
        ->assertRedirect('/cp/simple-commerce/tax/zones/united-kingdom/edit');
});

test('cant update tax zone when there is already a tax zone covering the same country', function () {
    // The one for editing
    TaxZone::make()
        ->id('united-states')
        ->name('United States')
        ->country('US')
        ->save();

    // The one we're checking doesn't already exist
    TaxZone::make()
        ->id('the-us')
        ->name('The US')
        ->country('US')
        ->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/zones/united-states/edit', [
            'name' => 'United States',
            'country' => 'US',
        ])
        ->assertSessionHasErrors();
});

test('cant update tax zone when there is already a tax zone covering the same country and region', function () {
    // The one for editing
    TaxZone::make()
        ->id('alaska')
        ->name('Alaska')
        ->country('US')
        ->region('us-ak')
        ->save();

    // The one we're checking doesn't already exist
    TaxZone::make()
        ->id('alaska-us')
        ->name('Alaska US')
        ->country('US')
        ->region('us-ak')
        ->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/zones/alaska/edit', [
            'name' => 'Alaska',
            'country' => 'US',
            'region' => 'us-ak',
        ])
        ->assertSessionHasErrors();
});

test('can destroy tax zone', function () {
    TaxZone::make()
        ->id('the-states')
        ->name('United States')
        ->country('US')
        ->save();

    $this
        ->actingAs(user())
        ->delete('/cp/simple-commerce/tax/zones/the-states/delete')
        ->assertJson(['success' => true]);
});

/**
 * This test ensures that any rates belonging to this tax zone
 * are cleaned up during the delete process.
 */
test('can destroy tax zone and delete assosiated rates', function () {
    TaxZone::make()
        ->id('the-states')
        ->name('United States')
        ->country('US')
        ->save();

    $taxCategory = TaxCategory::make()->id('abc')->name('General products');
    $taxCategory->save();

    $taxRate = TaxRate::make()->id('123')->name('US General products')->category('abc')->zone('the-states');
    $taxRate->save();

    expect($taxRate->path())->toBeFile();

    $this
        ->actingAs(user())
        ->delete('/cp/simple-commerce/tax/zones/the-states/delete')
        ->assertJson(['success' => true]);

    $this->assertFileDoesNotExist($taxRate->path());
});
