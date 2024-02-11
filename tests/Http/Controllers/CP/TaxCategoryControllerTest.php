<?php

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    if (File::exists(base_path('content/simple-commerce/tax-categories'))) {
        collect(File::allFiles(base_path('content/simple-commerce/tax-categories')))
            ->each(function ($file) {
                File::delete($file);
            });
    }
});

test('can get index', function () {
    TaxCategory::make()
        ->name('General')
        ->description('For most products (except essential items)')
        ->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/categories')
        ->assertOk()
        ->assertSee('General');
});

test('can create tax category', function () {
    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/categories/create')
        ->assertOk()
        ->assertSee('Create Tax Category')
        ->assertSee('Name')
        ->assertSee('Description');
});

test('can store tax category', function () {
    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/categories/create', [
            'name' => 'Special Products',
            'description' => 'Products that are very special.',
        ])
        ->assertRedirect();
});

test('can edit tax category', function () {
    TaxCategory::make()
        ->id('hmmmm')
        ->name('Hmmmm')
        ->description('Thinking about something....')
        ->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/tax/categories/hmmmm/edit')
        ->assertOk()
        ->assertSee('Hmmmm')
        ->assertSee('Thinking about something....');
});

test('can update tax category', function () {
    TaxCategory::make()
        ->id('whoop')
        ->name('Whoop')
        ->description('Whoop whoop woop!')
        ->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/tax/categories/whoop/edit', [
            'name' => 'Whoopsie',
            'description' => 'Whoopsie whoopsie whoopsie!',
        ])
        ->assertRedirect('/cp/simple-commerce/tax/categories/whoop/edit');
});

test('can destroy tax category', function () {
    TaxCategory::make()
        ->id('birthday')
        ->name('Birthday')
        ->description('Would you guess? Its my birthday.')
        ->save();

    $this
        ->actingAs(user())
        ->delete('/cp/simple-commerce/tax/categories/birthday/delete')
        ->assertJson(['success' => true]);
});

/**
 * This test ensures that any rates belonging to this tax category
 * are cleaned up during the delete process.
 */
test('can destroy tax category and delete assosiated rates', function () {
    TaxCategory::make()
        ->id('birthday')
        ->name('Birthday')
        ->description('Would you guess? Its my birthday.')
        ->save();

    $taxZone = TaxZone::make()->id('abc')->name('UK')->country('GB');
    $taxZone->save();

    $taxRate = TaxRate::make()->id('123')->name('UK Birthday')->category('birthday')->zone('abc');
    $taxRate->save();

    expect($taxRate->path())->toBeFile();

    $this
        ->actingAs(user())
        ->delete('/cp/simple-commerce/tax/categories/birthday/delete')
        ->assertJson(['success' => true]);

    $this->assertFileDoesNotExist($taxRate->path());
});
