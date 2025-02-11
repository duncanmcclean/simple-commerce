<?php

namespace Tests\Feature\Taxes;

use DuncanMcClean\SimpleCommerce\Facades\TaxClass;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreTaxZonesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('content/simple-commerce/tax-classes.yaml'));
        File::delete(base_path('content/simple-commerce/tax-zones.yaml'));
    }

    #[Test]
    public function can_store_tax_zone()
    {
        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();
        TaxClass::make()->handle('reduced')->set('name', 'Reduced')->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.tax-zones.store'), [
                'name' => 'United Kingdom',
                'type' => 'countries',
                'countries' => ['GB'],
                'rates' => [
                    'standard' => 20,
                    'reduced' => 5,
                ],
            ])
            ->assertOk()
            ->assertJson(['data' => ['id' => 'united-kingdom']]);

        $taxZone = TaxZone::find('united-kingdom');

        $this->assertEquals('United Kingdom', $taxZone->get('name'));
        $this->assertEquals('countries', $taxZone->get('type'));
        $this->assertEquals(['GB'], $taxZone->get('countries'));
        $this->assertEquals([
            'standard' => 20,
            'reduced' => 5,
        ], $taxZone->get('rates'));
    }

    #[Test]
    public function cant_store_tax_zone_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->post(cp_route('simple-commerce.tax-zones.store'), [
                'name' => 'United Kingdom',
                'type' => 'countries',
                'countries' => ['GB'],
                'rates' => [
                    'standard' => 20,
                    'reduced' => 5,
                ],
            ])
            ->assertRedirect('/cp');

        $this->assertNull(TaxZone::find('united-kingdom'));
    }

    #[Test]
    public function cant_store_tax_zone_with_the_same_country_as_another_tax_zone()
    {
        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();
        TaxClass::make()->handle('reduced')->set('name', 'Reduced')->save();

        TaxZone::make()->handle('uk-original')->data(['type' => 'countries', 'countries' => ['GB']])->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.tax-zones.store'), [
                'name' => 'United Kingdom',
                'type' => 'countries',
                'countries' => ['GB'],
                'rates' => [
                    'standard' => 20,
                    'reduced' => 5,
                ],
            ])
            ->assertSessionHasErrors('type');

        $this->assertNull(TaxZone::find('united-kingdom'));
    }

    #[Test]
    public function cant_store_tax_zone_with_the_same_state_as_another_tax_zone()
    {
        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();
        TaxClass::make()->handle('reduced')->set('name', 'Reduced')->save();

        TaxZone::make()->handle('uk-original')->data(['type' => 'states', 'countries' => ['GB'], 'states' => ['GLG', 'SLK']])->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.tax-zones.store'), [
                'name' => 'Glasgow(ish)',
                'type' => 'states',
                'countries' => ['GB'],
                'states' => ['GLG', 'SLK'],
                'rates' => [
                    'standard' => 20,
                    'reduced' => 5,
                ],
            ])
            ->assertSessionHasErrors('type');

        $this->assertNull(TaxZone::find('glasgowish'));
    }

    #[Test]
    public function cant_store_tax_zone_with_the_same_postcodes_as_another_tax_zone()
    {
        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();
        TaxClass::make()->handle('reduced')->set('name', 'Reduced')->save();

        TaxZone::make()->handle('uk-original')->data(['type' => 'postcodes', 'countries' => ['GB'], 'postcodes' => ['G*']])->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.tax-zones.store'), [
                'name' => 'Glasgow(ish)',
                'type' => 'postcodes',
                'countries' => ['GB'],
                'postcodes' => ['G*'],
                'rates' => [
                    'standard' => 20,
                    'reduced' => 5,
                ],
            ])
            ->assertSessionHasErrors('type');

        $this->assertNull(TaxZone::find('glasgowish'));
    }
}
