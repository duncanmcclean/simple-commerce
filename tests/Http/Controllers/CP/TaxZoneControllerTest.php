<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use Statamic\Facades\User;
use Illuminate\Support\Facades\File;

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
            ->assertRedirect();
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

    protected function user()
    {
        return User::make()
            ->makeSuper()
            ->email('joe.bloggs@example.com')
            ->set('password', 'secret')
            ->save();
    }
}
