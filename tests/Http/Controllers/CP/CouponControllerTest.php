<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\User;

class CouponControllerTest extends TestCase
{
    /** @test */
    public function can_get_index()
    {
        Coupon::make()
            ->id('random-id')
            ->code('fifty-friday')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Fifty Friday',
                'redeemed'           => 0,
                'minimum_cart_value' => null,
            ])
            ->save();

        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/coupons')
            ->assertOk()
            ->assertSee('Fifty Friday')
            ->assertSee('50% off');
    }

    /** @test */
    public function can_create_coupon()
    {
        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/coupons/create')
            ->assertOk()
            ->assertSee('Create Coupon');
    }

    /** @test */
    public function can_store_coupon()
    {
        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/coupons', [
                'code' => 'thursday-thirty',
                'type' => 'percentage',
                'value' => 30,
                'description' => '30% discount on a Thursday!',
            ])
            ->assertJsonStructure([
                'redirect',
            ])
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function can_edit_coupon()
    {
        $coupon = Coupon::make()
            ->id('random-id')
            ->code('fifty-friday')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Fifty Friday',
                'redeemed'           => 0,
                'minimum_cart_value' => null,
            ]);

        $coupon->save();

        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/coupons/random-id/edit')
            ->assertOk()
            ->assertSee('Edit Coupon')
            ->assertSee('Fifty Friday');
    }

    /** @test */
    public function can_update_coupon()
    {
        $coupon = Coupon::make()
            ->id('random-id')
            ->code('fifty-friday')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Fifty Friday',
                'redeemed'           => 0,
                'minimum_cart_value' => null,
            ]);

        $coupon->save();

        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/coupons/random-id', [
                'code' => 'fifty-friday',
                'type' => 'percentage',
                'value' => 51,
                'description' => 'You can actually get a 51% discount on Friday!',
            ])
            ->assertJsonStructure([
                'coupon',
            ]);

        $coupon->fresh();

        $this->assertSame($coupon->value(), 51);
        $this->assertSame($coupon->get('description'), 'You can actually get a 51% discount on Friday!');
    }

    /** @test */
    public function can_destroy_coupon()
    {
        Coupon::make()
            ->id('random-id')
            ->code('fifty-friday')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Fifty Friday',
                'redeemed'           => 0,
                'minimum_cart_value' => null,
            ])
            ->save();

        $this
            ->actingAs($this->user())
            ->delete('/cp/simple-commerce/coupons/random-id')
            ->assertRedirect('/cp/simple-commerce/coupons');
    }

    protected function user()
    {
        return User::make()
            ->makeSuper()
            ->email('joe.bloggs@example.com')
            ->set('password', 'secret')
            ->save();
    }
}
