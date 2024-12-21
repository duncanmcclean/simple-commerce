<?php

namespace Tests\Feature\Coupons;

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class EditCouponsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_edit_coupon()
    {
        $coupon = tap(Coupon::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.coupons.edit', $coupon->id()))
            ->assertOk()
            ->assertSee('FOOBAR25');
    }

    #[Test]
    public function cant_edit_coupon_without_permissions()
    {
        $coupon = tap(Coupon::make()->code('FOOBAR25'))->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.coupons.edit', $coupon->id()))
            ->assertRedirect('/cp');
    }
}
