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

    expect(30)->toBe($coupon->value());
    expect(true)->toBe($coupon->enabled());
    expect('30% discount on a Thursday!')->toBe($coupon->get('description'));
    expect(6500)->toBe($coupon->get('minimum_cart_value'));
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

    expect(51)->toBe($coupon->value());
    expect(false)->toBe($coupon->enabled());
    expect('You can actually get a 51% discount on Friday!')->toBe($coupon->get('description'));
    expect(7600)->toBe($coupon->get('minimum_cart_value'));
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

    expect(50)->toBe($coupon->value());
    expect('Fifty Friday')->toBe($coupon->get('description'));
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
