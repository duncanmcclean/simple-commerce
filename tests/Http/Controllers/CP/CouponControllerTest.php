<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\User;

uses(TestCase::class);

test('can get index', function () {
    Coupon::make()
        ->id('random-id')
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ])
        ->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/coupons')
        ->assertOk()
        ->assertSee('Fifty Friday')
        ->assertSee('50%');
});

test('can create coupon', function () {
    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/coupons/create')
        ->assertOk()
        ->assertSee('Create Coupon');
});

test('can store coupon', function () {
    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/coupons', [
            'code' => 'thursday-thirty',
            'type' => 'percentage',
            'value' => 30,
            'description' => '30% discount on a Thursday!',
            'minimum_cart_value' => '65.00',
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
    $this->assertSame($coupon->get('minimum_cart_value'), 6500);
});

test('cant store coupon where a coupon already exists with the provided code', function () {
    Coupon::make()
        ->id('random-id')
        ->code('tuesday-subway')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ])
     ->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/coupons', [
            'code' => 'tuesday-subway',
            'type' => 'percentage',
            'value' => 30,
            'description' => '30% discount on a Tuesday!',
        ])
        ->assertSessionHasErrors('code');
});

test('cant store coupon if type is percentage and value is greater than 100', function () {
    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/coupons', [
            'code' => 'thursday-thirty',
            'type' => 'percentage',
            'value' => 150,
            'description' => '30% discount on a Thursday!',
        ])
        ->assertSessionHasErrors('value');
});

test('can edit coupon', function () {
    $coupon = Coupon::make()
        ->id('random-id')
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();

    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/coupons/random-id/edit')
        ->assertOk()
        ->assertSee('Edit Coupon')
        ->assertSee('Fifty Friday');
});

test('can update coupon', function () {
    $coupon = Coupon::make()
        ->id('random-id')
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();

    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/coupons/random-id', [
            'code' => 'fifty-friday',
            'type' => 'percentage',
            'value' => 51,
            'description' => 'You can actually get a 51% discount on Friday!',
            'enabled' => false,
            'minimum_cart_value' => '76.00',
        ])
        ->assertJsonStructure([
            'coupon',
        ]);

    $coupon->fresh();

    $this->assertSame($coupon->value(), 51);
    $this->assertSame($coupon->enabled(), false);
    $this->assertSame($coupon->get('description'), 'You can actually get a 51% discount on Friday!');
    $this->assertSame($coupon->get('minimum_cart_value'), 7600);
});

test('cant update coupon if type is percentage and value is greater than 100', function () {
    $coupon = Coupon::make()
        ->id('random-id')
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();

    $this
        ->actingAs(user())
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
});

test('can destroy coupon', function () {
    Coupon::make()
        ->id('random-id')
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ])
        ->save();

    $this
        ->actingAs(user())
        ->delete('/cp/simple-commerce/coupons/random-id')
        ->assertRedirect('/cp/simple-commerce/coupons');
});

// Helpers
function user()
{
    return User::make()
        ->makeSuper()
        ->email('joe.bloggs@example.com')
        ->set('password', 'secret')
        ->save();
}
