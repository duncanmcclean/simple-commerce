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

class StoreTaxClassesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('content/simple-commerce/tax-classes.yaml'));
    }

    #[Test]
    public function can_store_tax_class()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.tax-classes.store'), [
                'name' => 'Standard',
            ])
            ->assertOk()
            ->assertJson(['data' => ['id' => 'standard']]);

        $taxClass = TaxClass::find('standard');
        $this->assertEquals('Standard', $taxClass->get('name'));
    }

    #[Test]
    public function cant_store_tax_class_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->post(cp_route('simple-commerce.tax-classes.store'), [
                'name' => 'Standard',
            ])
            ->assertRedirect('/cp');

        $this->assertNull(TaxClass::find('standard'));
    }
}
