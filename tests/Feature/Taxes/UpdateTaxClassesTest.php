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

class UpdateTaxClassesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('content/simple-commerce/tax-classes.yaml'));
    }

    #[Test]
    public function can_update_tax_class()
    {
        $taxClass = tap(TaxClass::make()->handle('standard')->set('name', 'Standard'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('simple-commerce.tax-classes.update', $taxClass->handle()), [
                'name' => 'Standard Tax Rate',
            ])
            ->assertOk()
            ->assertJson(['data' => ['id' => 'standard']]);

        $taxClass = TaxClass::find('standard');
        $this->assertEquals('Standard Tax Rate', $taxClass->get('name'));
    }

    #[Test]
    public function cant_update_tax_class_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $taxClass = tap(TaxClass::make()->handle('standard')->set('name', 'Standard'))->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('simple-commerce.tax-classes.update', $taxClass->handle()), [
                'name' => 'Standard Tax Rate',
            ])
            ->assertRedirect('/cp');

        $taxClass = TaxClass::find('standard');
        $this->assertEquals('Standard', $taxClass->get('name'));
    }
}
