<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CouponFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

uses(TestCase::class);
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

    $this->assertTrue($getIndexItems instanceof Collection);

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

    $this->assertIsArray($getColumns);

    $this->assertTrue($getColumns[0] instanceof Column);
    $this->assertSame($getColumns[0]->field(), 'code');
    $this->assertSame($getColumns[0]->label(), 'Code');

    $this->assertTrue($getColumns[1] instanceof Column);
    $this->assertSame($getColumns[1]->field(), 'discount');
    $this->assertSame($getColumns[1]->label(), 'Discount');

    $this->assertTrue($getColumns[2] instanceof Column);
    $this->assertSame($getColumns[2]->field(), 'redeemed');
    $this->assertSame($getColumns[2]->label(), 'Redeemed');
});

test('can return as item array', function () {
    $toItemArray = $this->fieldtype->toItemArray('foo');

    $this->assertIsArray($toItemArray);

    $this->assertSame($toItemArray, [
        'id' => 'foo',
        'title' => 'TUPLE',
    ]);
});

test('can preprocess index', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex('foo');

    $this->assertTrue($preProcessIndex instanceof Collection);
    $this->assertCount(1, $preProcessIndex);

    $this->assertSame($preProcessIndex[0], [
        'id' => 'foo',
        'title' => 'TUPLE',
        'edit_url' => 'http://localhost/cp/simple-commerce/coupons/foo/edit',
    ]);
});

test('can preprocess index with no country', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(null);

    $this->assertNull($preProcessIndex);
});

test('can preprocess with multiple countries', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(['foo', 'rad']);

    $this->assertTrue($preProcessIndex instanceof Collection);
    $this->assertCount(2, $preProcessIndex);

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
