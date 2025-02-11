<?php

namespace Tests\Feature\Taxes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewTaxClassesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function can_create_tax_classes()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.tax-classes.index'))
            ->assertOk()
            ->assertSee('Tax Classes');
    }

    #[Test]
    public function cant_view_tax_classes_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.tax-classes.index'))
            ->assertRedirect('/cp');
    }
}
