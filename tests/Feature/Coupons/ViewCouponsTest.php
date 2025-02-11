<?php

namespace Tests\Feature\Coupons;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\TestCase;

class ViewCouponsTest extends TestCase
{
    #[Test]
    public function can_view_coupons()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.coupons.index'))
            ->assertOk();
    }

    #[Test]
    public function cant_view_coupons_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.coupons.index'))
            ->assertRedirect('/cp');
    }
}
