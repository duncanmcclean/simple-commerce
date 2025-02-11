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

class EditTaxClassesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('content/simple-commerce/tax-classes.yaml'));
    }

    #[Test]
    public function can_edit_tax_class()
    {
        $taxClass = tap(TaxClass::make()->handle('standard')->set('name', 'Standard'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.tax-classes.edit', $taxClass->handle()))
            ->assertOk()
            ->assertSee('Standard');
    }

    #[Test]
    public function cant_edit_tax_class_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $taxClass = tap(TaxClass::make()->handle('standard')->set('name', 'Standard'))->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.tax-classes.edit', $taxClass->handle()))
            ->assertRedirect('/cp');
    }
}
