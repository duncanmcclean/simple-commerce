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

class StoreCouponsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_store_coupon()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.coupons.store'), [
                'code' => 'BAZQUX50',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertOk()
            ->assertSee('BAZQUX50');

        $coupon = Coupon::findByCode('BAZQUX50');

        $this->assertEquals($coupon->code(), 'BAZQUX50');
        $this->assertEquals($coupon->type(), CouponType::Percentage);
        $this->assertEquals($coupon->amount(), 50);
        $this->assertEquals($coupon->get('customer_eligibility'), 'all');
    }

    #[Test]
    public function cant_store_coupon_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->post(cp_route('simple-commerce.coupons.store'), [
                'code' => 'BAZQUX50',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertRedirect('/cp');

        $this->assertNull(Coupon::findByCode('BAZQUX50'));
    }

    #[Test]
    public function cant_store_coupon_with_invalid_characters_in_code()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.coupons.store'), [
                'code' => 'FOOB;//-\(R',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNull(Coupon::findByCode('FOOB;//-\(R'));
    }

    #[Test]
    public function cant_store_coupon_with_lowercase_code()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.coupons.store'), [
                'code' => 'foobar',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNull(Coupon::findByCode('foobar'));
    }

    #[Test]
    public function cant_store_coupon_with_duplicate_code()
    {
        Coupon::make()->code('FOOBAR')->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.coupons.store'), [
                'code' => 'FOOBAR',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');
    }
}
