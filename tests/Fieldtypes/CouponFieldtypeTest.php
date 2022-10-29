<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CouponFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

class CouponFieldtypeTest extends TestCase
{
    protected $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

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
    }

    /** @test */
    public function can_get_index_items()
    {
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
    }

    /** @test */
    public function can_get_columns()
    {
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
    }

    /** @test */
    public function can_return_as_item_array()
    {
        $toItemArray = $this->fieldtype->toItemArray('foo');

        $this->assertIsArray($toItemArray);

        $this->assertSame($toItemArray, [
            'id' => 'foo',
            'title' => 'TUPLE',
        ]);
    }

    /** @test */
    public function can_preprocess_index()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex('foo');

        $this->assertTrue($preProcessIndex instanceof Collection);
        $this->assertCount(1, $preProcessIndex);

        $this->assertSame($preProcessIndex[0], [
            'id' => 'foo',
            'title' => 'TUPLE',
            'edit_url' => 'http://localhost/cp/simple-commerce/coupons/foo/edit',
        ]);
    }

    /** @test */
    public function can_preprocess_index_with_no_country()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex(null);

        $this->assertNull($preProcessIndex);
    }

    /** @test */
    public function can_preprocess_with_multiple_countries()
    {
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
    }
}
