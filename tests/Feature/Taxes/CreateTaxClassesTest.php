<?php

namespace Tests\Feature\Taxes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateTaxClassesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function can_create_tax_class()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.tax-classes.create'))
            ->assertOk()
            ->assertSee('Create Tax Class');
    }

    #[Test]
    public function cant_create_tax_class_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.tax-classes.create'))
            ->assertRedirect('/cp');
    }
}
