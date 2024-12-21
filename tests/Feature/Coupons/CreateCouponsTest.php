<?php

namespace Tests\Feature\Coupons;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateCouponsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_create_coupon()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.coupons.create'))
            ->assertOk()
            ->assertSee('Create Coupon');
    }

    #[Test]
    public function cant_create_coupon_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.coupons.create'))
            ->assertRedirect('/cp');
    }
}
