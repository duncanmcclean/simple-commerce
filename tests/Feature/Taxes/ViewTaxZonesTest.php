<?php

namespace Tests\Feature\Taxes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewTaxZonesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function can_create_tax_zones()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.tax-zones.index'))
            ->assertOk()
            ->assertSee('Tax Zones');
    }

    #[Test]
    public function cant_view_tax_zones_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.tax-zones.index'))
            ->assertRedirect('/cp');
    }
}
