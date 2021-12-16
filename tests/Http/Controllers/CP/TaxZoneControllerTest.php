<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Statamic\Facades\User;

class TaxZoneControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        collect(File::allFiles(base_path('content/simple-commerce/tax-zones')))
            ->each(function ($file) {
                File::delete($file);
            });
    }

    /** @test */
    public function can_get_index()
    {
        TaxZone::make()
            ->name('United Kingdom (apart from the fact Scotland wants to leave)')
            ->country('GB')
            ->save();

        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax-zones')
            ->assertOk()
            ->assertSee('United Kingdom (apart from the fact')
            ->assertSee('United Kingdom');
    }

    /** @test */
    public function can_create_tax_zone()
    {
        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax-zones/create')
            ->assertOk()
            ->assertSee('Create Tax Zone')
            ->assertSee('Name')
            ->assertSee('Country')
            ->assertSee('region-selector');
    }

    /** @test */
    public function can_store_tax_zone()
    {
        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax-zones/create', [
                'name' => 'Special Products',
                'country' => 'DE',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function cant_store_tax_zone_when_there_is_already_a_tax_zone_covering_the_same_country()
    {
        TaxZone::make()
            ->id('the-us')
            ->name('The US')
            ->country('US')
            ->save();

        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax-zones/create', [
                'name' => 'United States',
                'country' => 'US',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors();
    }

    /** @test */
    public function cant_store_tax_zone_when_there_is_already_a_tax_zone_covering_the_same_country_and_region()
    {
        TaxZone::make()
            ->id('the-us')
            ->name('The Alaska State')
            ->country('US')
            ->region('us-ak')
            ->save();

        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax-zones/create', [
                'name' => 'Alaska',
                'country' => 'US',
                'region' => 'us-ak',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors();
    }

    /** @test */
    public function can_edit_tax_zone()
    {
        TaxZone::make()
            ->id('united-kingdom')
            ->name('United Kingdom (apart from the fact Scotland wants to leave)')
            ->country('GB')
            ->save();

        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax-zones/united-kingdom/edit')
            ->assertOk()
            ->assertSee('United Kingdom (apart from the fact')
            ->assertSee('United Kingdom');
    }

    /** @test */
    public function can_update_tax_zone()
    {
        TaxZone::make()
            ->id('united-kingdom')
            ->name('United Kingdom (apart from the fact Scotland wants to leave)')
            ->country('GB')
            ->save();

        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax-zones/united-kingdom/edit', [
                'name' => 'The United Kingdom of Scotland, Northern Ireland, Wales and England',
                'country' => 'GB',
            ])
            ->assertRedirect('/cp/simple-commerce/tax-zones/united-kingdom/edit');
    }

    /** @test */
    public function cant_update_tax_zone_when_there_is_already_a_tax_zone_covering_the_same_country()
    {
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
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax-zones/united-states/edit', [
                'name' => 'United States',
                'country' => 'US',
            ])
            ->assertSessionHasErrors();
    }

    /** @test */
    public function cant_update_tax_zone_when_there_is_already_a_tax_zone_covering_the_same_country_and_region()
    {
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
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax-zones/alaska/edit', [
                'name' => 'Alaska',
                'country' => 'US',
                'region' => 'us-ak',
            ])
            ->assertSessionHasErrors();
    }

    /** @test */
    public function can_destroy_tax_zone()
    {
        TaxZone::make()
            ->id('the-states')
            ->name('United States')
            ->country('US')
            ->save();

        $this
            ->actingAs($this->user())
            ->delete('/cp/simple-commerce/tax-zones/the-states/delete')
            ->assertRedirect('/cp/simple-commerce/tax-zones');
    }

    /**
     * @test
     *
     * This test ensures that any rates belonging to this tax zone
     * are cleaned up during the delete process.
     */
    public function can_destroy_tax_zone_and_delete_assosiated_rates()
    {
        TaxZone::make()
            ->id('the-states')
            ->name('United States')
            ->country('US')
            ->save();

        $taxCategory = TaxCategory::make()->id('abc')->name('General products');
        $taxCategory->save();

        $taxRate = TaxRate::make()->id('123')->name('US General products')->category('abc')->zone('the-states');
        $taxRate->save();

        $this->assertFileExists($taxRate->path());

        $this
            ->actingAs($this->user())
            ->delete('/cp/simple-commerce/tax-zones/the-states/delete')
            ->assertRedirect('/cp/simple-commerce/tax-zones');

        $this->assertFileDoesNotExist($taxRate->path());
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
