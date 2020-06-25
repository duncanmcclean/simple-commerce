<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CouponControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        factory(Currency::class)->create();
    }

    /** @test */
    public function can_get_coupons()
    {
        $coupons = factory(Coupon::class, 3)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('coupons.index'))
            ->assertOk()
            ->assertSee($coupons[0]['name'])
            ->assertSee($coupons[1]['name'])
            ->assertSee($coupons[2]['name']);
    }

    /** @test */
    public function can_create_coupon()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('coupons.create'))
            ->assertOk()
            ->assertSee('publish-form');
    }

    /** @test */
    public function can_store_coupon()
    {
        $this
            ->actAsSuper()
            ->post(cp_route('coupons.store'), [
                'name'          => 'Five Dollar Discount',
                'code'          => 'fivedollar',
                'type'          => 'fixed_discount',
                'value'         => '5',
                'minimum_total' => '0',
                'total_uses'    => '0',
                'start_date'    => '',
                'end_date'      => '',
            ])
            ->assertOk();

        $this
            ->assertDatabaseHas('coupons', [
                'name' => 'Five Dollar Discount',
                'code' => 'fivedollar',
            ]);
    }

    /** @test */
    public function can_edit_coupon()
    {
        $coupon = factory(Coupon::class)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('coupons.edit', ['coupon' => $coupon->uuid]))
            ->assertOk()
            ->assertSee($coupon->name);
    }

    /** @test */
    public function can_update_coupon()
    {
        $coupon = factory(Coupon::class)->create();

        $this
            ->actAsSuper()
            ->post(cp_route('coupons.update', ['coupon' => $coupon->uuid]), [
                'name'          => 'Tenner Discount',
                'code'          => 'tenner',
                'type'          => 'fixed_discount',
                'value'         => '5',
                'minimum_total' => '0',
                'total_uses'    => '0',
                'start_date'    => '',
                'end_date'      => '',
            ])
            ->assertOk()
            ->assertSee('Tenner Discount')
            ->assertSee('tenner');

        $this
            ->assertDatabaseMissing('coupons', [
                'name' => $coupon->name,
                'code' => $coupon->code,
            ])
            ->assertDatabaseHas('coupons', [
                'name' => 'Tenner Discount',
                'code' => 'tenner',
            ]);
    }

    /** @test */
    public function can_destroy_coupon()
    {
        $coupon = factory(Coupon::class)->create();

        $this
            ->actAsSuper()
            ->delete(cp_route('coupons.destroy', ['coupon' => $coupon->uuid]))
            ->assertOk();

        $this
            ->assertDatabaseMissing('coupons', [
                'uuid' => $coupon->uuid,
                'name' => $coupon->name,
            ]);
    }
}
