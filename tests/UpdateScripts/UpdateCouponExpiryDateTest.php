<?php

use Carbon\Carbon;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\UpdateScripts\v6_0\UpdateCouponExpiryDate;
use Spatie\TestTime\TestTime;

it('sets the expiry date for a disabled coupon', function () {
    TestTime::freeze(Carbon::parse('2024-01-01'));

    $coupon = Coupon::make()
        ->code('test')
        ->type('percentage')
        ->value(10)
        ->set('enabled', false)
        ->set('expires_at', null)
        ->save();

    $coupon->save();

    (new UpdateCouponExpiryDate('duncanmcclean/simple-commerce', '6.0.0'))->update();

    $coupon->fresh();

    expect($coupon->get('expires_at'))->toBe('2024-01-01');
});

it('does not set the expiry date for an enabled coupon', function () {
    TestTime::freeze(Carbon::parse('2024-01-01'));

    $coupon = Coupon::make()
        ->code('test')
        ->type('percentage')
        ->value(10)
        ->set('enabled', true)
        ->set('expires_at', null)
        ->save();

    $coupon->save();

    (new UpdateCouponExpiryDate('duncanmcclean/simple-commerce', '6.0.0'))->update();

    $coupon->fresh();

    expect($coupon->get('expires_at'))->toBeNull();
});

it('does not set the expiry date for an already expired coupon', function () {
    TestTime::freeze(Carbon::parse('2024-01-01'));

    $coupon = Coupon::make()
        ->code('test')
        ->type('percentage')
        ->value(10)
        ->set('enabled', true)
        ->set('expires_at', '2023-12-12')
        ->save();

    $coupon->save();

    (new UpdateCouponExpiryDate('duncanmcclean/simple-commerce', '6.0.0'))->update();

    $coupon->fresh();

    expect($coupon->get('expires_at'))->toBe('2023-12-12');
});
