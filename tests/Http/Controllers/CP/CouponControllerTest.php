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
            ->assertSee('50%');
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
                'enabled' => true,
            ])
            ->assertJsonStructure([
                'redirect',
            ])
            ->assertSessionHasNoErrors();

        $coupon = Coupon::findByCode('thursday-thirty');

        $this->assertSame($coupon->value(), 30);
        $this->assertSame($coupon->enabled(), true);
        $this->assertSame($coupon->get('description'), '30% discount on a Thursday!');
    }

    /** @test */
    public function cant_store_coupon_where_a_coupon_already_exists_with_the_provided_code()
    {
        Coupon::make()
            ->id('random-id')
            ->code('tuesday-subway')
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
            ->post('/cp/simple-commerce/coupons', [
                'code' => 'tuesday-subway',
                'type' => 'percentage',
                'value' => 30,
                'description' => '30% discount on a Tuesday!',
            ])
            ->assertSessionHasErrors('code');
    }

    /** @test */
    public function cant_store_coupon_if_type_is_percentage_and_value_is_greater_than_100()
    {
        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/coupons', [
                'code' => 'thursday-thirty',
                'type' => 'percentage',
                'value' => 150,
                'description' => '30% discount on a Thursday!',
            ])
            ->assertSessionHasErrors('value');
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
                'enabled' => false,
            ])
            ->assertJsonStructure([
                'coupon',
            ]);

        $coupon->fresh();

        $this->assertSame($coupon->value(), 51);
        $this->assertSame($coupon->enabled(), false);
        $this->assertSame($coupon->get('description'), 'You can actually get a 51% discount on Friday!');
    }

    /** @test */
    public function cant_update_coupon_if_type_is_percentage_and_value_is_greater_than_100()
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
                'value' => 110,
                'description' => 'You can actually get a 51% discount on Friday!',
            ])
            ->assertSessionHasErrors('value');

        $coupon->fresh();

        $this->assertSame($coupon->value(), 50);
        $this->assertSame($coupon->get('description'), 'Fifty Friday');
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
