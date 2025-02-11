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

class EditTaxZonesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('content/simple-commerce/tax-classes.yaml'));
        File::delete(base_path('content/simple-commerce/tax-zones.yaml'));
    }

    #[Test]
    public function can_edit_tax_zone()
    {
        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();

        $taxZone = tap(TaxZone::make()->handle('united-kingdom')->data([
            'name' => 'United Kingdom',
            'type' => 'countries',
            'countries' => ['GB'],
            'rates' => ['standard' => 20],
        ]))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.tax-zones.edit', $taxZone->handle()))
            ->assertOk()
            ->assertSee('United Kingdom');
    }

    #[Test]
    public function cant_edit_tax_zone_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();

        $taxZone = tap(TaxZone::make()->handle('united-kingdom')->data([
            'name' => 'United Kingdom',
            'type' => 'countries',
            'countries' => ['GB'],
            'rates' => ['standard' => 20],
        ]))->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.tax-zones.edit', $taxZone->handle()))
            ->assertRedirect('/cp');
    }
}
