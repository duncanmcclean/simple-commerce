<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;

test('can get index', function () {
    $this
        ->actingAs(user())
        ->get('/cp/simple-commerce/coupons')
        ->assertOk();
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
            'value' => [
                'mode' => 'percentage',
                'value' => 30,
            ],
            'description' => '30% discount on a Thursday!',
            'minimum_cart_value' => '65.00',
            'enabled' => true,
            'expires_at' => null,
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

test('can store coupon with expiry date', function () {
    $this
        ->actingAs(user())
        ->post('/cp/simple-commerce/coupons', [
            'code' => 'thursday-thirty-two',
            'type' => 'percentage',
            'value' => [
                'mode' => 'percentage',
                'value' => 32,
            ],
            'description' => '30% discount on a Thursday!',
            'minimum_cart_value' => '65.00',
            'enabled' => true,
            'expires_at' => [
                'date' => '2024-01-01',
                'time' => null,
            ],
        ])
        ->assertJsonStructure([
            'redirect',
        ])
        ->assertSessionHasNoErrors();

    $coupon = Coupon::findByCode('thursday-thirty-two');

    expect($coupon->get('expires_at'))->toBe('2024-01-01');
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
            'value' => [
                'mode' => 'percentage',
                'value' => 30,
            ],
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
            'value' => [
                'mode' => 'percentage',
                'value' => 51,
            ],
            'description' => 'You can actually get a 51% discount on Friday!',
            'enabled' => false,
            'minimum_cart_value' => '76.00',
            'expires_at' => null,
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

test('can update coupon with expriry date', function () {
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
            'value' => [
                'mode' => 'percentage',
                'value' => 51,
            ],
            'description' => 'You can actually get a 51% discount on Friday!',
            'enabled' => false,
            'minimum_cart_value' => '76.00',
            'expires_at' => [
                'date' => '2024-01-01',
                'time' => null,
            ],
        ])
        ->assertJsonStructure([
            'coupon',
        ]);

    $coupon->fresh();

    expect($coupon->get('expires_at'))->toBe('2024-01-01');
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
            'value' => [
                'mode' => 'percentage',
                'value' => 110,
            ],
            'description' => 'You can actually get a 51% discount on Friday!',
        ])
        ->assertSessionHasErrors('value.value');

    $coupon->fresh();

    expect(50)->toBe($coupon->value());
    expect('Fifty Friday')->toBe($coupon->get('description'));
});
