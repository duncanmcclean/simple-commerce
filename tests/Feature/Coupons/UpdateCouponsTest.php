<?php

namespace Tests\Feature\Coupons;

use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateCouponsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_update_coupon()
    {
        $coupon = tap(Coupon::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('simple-commerce.coupons.update', $coupon->id()), [
                'code' => 'BAZQUX50',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertOk()
            ->assertSee('BAZQUX50');

        $coupon = $coupon->fresh();

        $this->assertEquals($coupon->code(), 'BAZQUX50');
        $this->assertEquals($coupon->type(), CouponType::Percentage);
        $this->assertEquals($coupon->amount(), 50);
        $this->assertEquals($coupon->get('customer_eligibility'), 'all');
    }

    #[Test]
    public function cant_update_coupon_without_permissions()
    {
        $coupon = tap(Coupon::make()->code('FOOBAR25'))->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('simple-commerce.coupons.update', $coupon->id()), [
                'code' => 'BAZQUX50',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertRedirect('/cp');
    }

    #[Test]
    public function cant_update_coupon_with_invalid_characters_in_code()
    {
        $coupon = tap(Coupon::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('simple-commerce.coupons.update', $coupon->id()), [
                'code' => 'FOOB;//-\(R',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $coupon = $coupon->fresh();

        $this->assertNotEquals($coupon->code(), 'FOOB;//-\(R');
    }

    #[Test]
    public function cant_update_coupon_with_lowercase_code()
    {
        $coupon = tap(Coupon::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('simple-commerce.coupons.update', $coupon->id()), [
                'code' => 'foobar',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $coupon = $coupon->fresh();

        $this->assertNotEquals($coupon->code(), 'foobar');
    }

    #[Test]
    public function cant_update_coupon_with_duplicate_code()
    {
        Coupon::make()->code('FOOBAR')->save();
        $coupon = tap(Coupon::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('simple-commerce.coupons.update', $coupon->id()), [
                'code' => 'FOOBAR',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');
    }
}