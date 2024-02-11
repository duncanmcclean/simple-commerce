<?php

use DuncanMcClean\SimpleCommerce\Actions\Delete;
use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use Statamic\Facades\Entry;

beforeEach(function () {
    $this->action = new Delete();
});

test('is visible to coupons', function () {
    $coupon = tap(Coupon::make()->code('TESTCOUPON')->type(CouponType::Percentage)->value(10))->save();

    $action = $this->action->visibleTo($coupon);

    expect($action)->toBeTrue();
});

test('is not visible to entries', function () {
    $entry = tap(Entry::make()->collection('products')->slug('test-product'))->save();

    $action = $this->action->visibleTo($entry);

    expect($action)->toBeFalse();
});

test('coupon can be deleted', function () {
    $coupon = tap(Coupon::make()->code('TESTCOUPON')->type(CouponType::Percentage)->value(10))->save();

    $this->action->run(collect([$coupon]), null);

    expect(Coupon::findByCode('TESTCOUPON'))->toBeNull();
});
