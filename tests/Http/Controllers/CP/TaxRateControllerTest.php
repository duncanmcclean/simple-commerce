<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Statamic\Facades\User;

class TaxRateControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        collect(File::allFiles(base_path('content/simple-commerce/tax-rates')))
            ->each(function ($file) {
                File::delete($file);
            });
    }

    /** @test */
    public function can_get_index()
    {
        TaxZone::make()->id('the-uk')->name('The UK')->country('GB')->save();
        TaxCategory::make()->id('standard')->name('Standard Products')->description('For all the standard products')->save();

        TaxRate::make()
            ->name('UK - Standard Products')
            ->rate(20)
            ->zone('the-uk')
            ->category('standard')
            ->save();

        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax/rates')
            ->assertOk()
            ->assertSee('UK - Standard Products')
            ->assertSee('20%')
            ->assertSee('The UK');
    }

    /** @test */
    public function can_create_tax_rate()
    {
        TaxCategory::make()->id('standard')->name('Standard Products')->description('For all the standard products')->save();

        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax/rates/create?taxCategory=standard')
            ->assertOk()
            ->assertSee('Create Tax Rate')
            ->assertSee('Name')
            ->assertSee('Rate')
            ->assertSee('Tax Zone')
            ->assertSee('Include in price?');
    }

    /** @test */
    public function can_store_tax_rate()
    {
        TaxZone::make()->id('the-uk')->name('The UK')->country('GB')->save();
        TaxCategory::make()->id('special')->name('Special Products')->description('For all the special products')->save();

        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax/rates/create', [
                'name' => 'UK - Special',
                'rate' => 5,
                'category' => 'special',
                'zone' => 'the-uk',
                'include_in_price' => 'true',
            ])
            ->assertRedirect();
    }

    /** @test */
    public function can_edit_tax_rate()
    {
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
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax/rates/uk-standard-products/edit')
            ->assertOk()
            ->assertSee('UK - Standard Products')
            ->assertSee('20')
            ->assertSee('the-uk')
            ->assertSee('standard')
            ->assertSee('name="include_in_price" value="true"', false);
    }

    /** @test */
    public function can_update_tax_rate()
    {
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
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax/rates/uk-standard-products/edit', [
                'name' => 'UK - Standard Products (15% for COVID)',
                'rate' => 15,
                'zone' => 'the-uk',
                'category' => 'standard',
                'include_in_price' => 'true',
            ])
            ->assertRedirect('/cp/simple-commerce/tax/rates/uk-standard-products/edit');
    }

    /** @test */
    public function can_destroy_tax_rate()
    {
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
            ->actingAs($this->user())
            ->delete('/cp/simple-commerce/tax/rates/uk-standard-products/delete')
            ->assertRedirect('/cp/simple-commerce/tax/rates');
    }

    protected function user()
    {
        return User::make()
            ->makeSuper()
            ->email('joe.bloggs@example.com')
            ->set('password', 'secret')
            ->save();
    }
}
