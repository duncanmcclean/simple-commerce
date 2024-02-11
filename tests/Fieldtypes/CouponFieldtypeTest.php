<?php

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Fieldtypes\CouponFieldtype;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\Invader;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

beforeEach(function () {
    $this->fieldtype = new CouponFieldtype;

    Coupon::all()->each->delete();

    Coupon::make()
        ->id('blah')
        ->code('GITHUB')
        ->type('percentage')
        ->value(10)
        ->save();

    Coupon::make()
        ->id('foo')
        ->code('TUPLE')
        ->type('percentage')
        ->value(15)
        ->save();

    Coupon::make()
        ->id('rad')
        ->code('STATAMIC')
        ->type('percentage')
        ->value(60)
        ->data([
            'redeemed' => 25,
        ])
        ->save();
});

test('can get index items', function () {
    $getIndexItems = $this->fieldtype->getIndexItems(new Request());

    expect($getIndexItems instanceof Collection)->toBeTrue();

    $this->assertSame($getIndexItems->first(), [
        'id' => 'blah',
        'code' => 'GITHUB',
        'discount' => '10%',
        'redeemed' => '0 times',
    ]);

    $this->assertSame($getIndexItems->last(), [
        'id' => 'rad',
        'code' => 'STATAMIC',
        'discount' => '60%',
        'redeemed' => '25 times',
    ]);
});

test('can get columns', function () {
    $getColumns = (new Invader($this->fieldtype))->getColumns();

    expect($getColumns)->toBeArray();

    expect($getColumns[0] instanceof Column)->toBeTrue();
    expect('code')->toBe($getColumns[0]->field());
    expect('Code')->toBe($getColumns[0]->label());

    expect($getColumns[1] instanceof Column)->toBeTrue();
    expect('discount')->toBe($getColumns[1]->field());
    expect('Discount')->toBe($getColumns[1]->label());

    expect($getColumns[2] instanceof Column)->toBeTrue();
    expect('redeemed')->toBe($getColumns[2]->field());
    expect('Redeemed')->toBe($getColumns[2]->label());
});

test('can return as item array', function () {
    $toItemArray = $this->fieldtype->toItemArray('foo');

    expect($toItemArray)->toBeArray();

    $this->assertSame($toItemArray, [
        'id' => 'foo',
        'title' => 'TUPLE',
    ]);
});

test('can preprocess index', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex('foo');

    expect($preProcessIndex instanceof Collection)->toBeTrue();
    expect($preProcessIndex)->toHaveCount(1);

    $this->assertSame($preProcessIndex[0], [
        'id' => 'foo',
        'title' => 'TUPLE',
        'edit_url' => 'http://localhost/cp/simple-commerce/coupons/foo/edit',
    ]);
});

test('can preprocess index with no country', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(null);

    expect($preProcessIndex)->toBeNull();
});

test('can preprocess with multiple countries', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(['foo', 'rad']);

    expect($preProcessIndex instanceof Collection)->toBeTrue();
    expect($preProcessIndex)->toHaveCount(2);

    $this->assertSame($preProcessIndex[0], [
        'id' => 'foo',
        'title' => 'TUPLE',
        'edit_url' => 'http://localhost/cp/simple-commerce/coupons/foo/edit',
    ]);

    $this->assertSame($preProcessIndex[1], [
        'id' => 'rad',
        'title' => 'STATAMIC',
        'edit_url' => 'http://localhost/cp/simple-commerce/coupons/rad/edit',
    ]);
});
