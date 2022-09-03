<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Coupons;

use DoubleThreeDigital\SimpleCommerce\Coupons\Coupon as CouponsCoupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CouponTest extends TestCase
{
    /** @test */
    public function can_create()
    {
        $create = Coupon::make()
            ->code('test')
            ->type('percentage')
            ->value(10)
            ->data([
                'foo' => 'bar',
                'baz' => 'qux',
            ]);

        $create->save();

        $this->assertTrue($create instanceof CouponsCoupon);

        $this->assertNotNull($create->id());

        $this->assertSame($create->code(), 'test');
        $this->assertSame($create->type(), 'percentage');
        $this->assertSame($create->value(), 10);
        $this->assertSame($create->get('foo'), 'bar');
        $this->assertSame($create->get('baz'), 'qux');
    }

    /** @test */
    public function can_find_by_id()
    {
        $create = Coupon::make()
            ->id('this-is-a-test-id')
            ->code('test')
            ->type('percentage')
            ->value(10)
            ->data([
                'foo' => 'bar',
                'baz' => 'qux',
            ]);

        $create->save();

        $coupon = Coupon::find('this-is-a-test-id');

        $this->assertTrue($create instanceof CouponsCoupon);

        $this->assertSame($coupon->id(), 'this-is-a-test-id');
        $this->assertSame($create->code(), 'test');
        $this->assertSame($create->type(), 'percentage');
        $this->assertSame($create->value(), 10);
        $this->assertSame($create->get('foo'), 'bar');
        $this->assertSame($create->get('baz'), 'qux');
    }

    /** @test */
    public function can_find_by_code()
    {
        $create = Coupon::make()
            ->id('this-is-a-test-id')
            ->code('test')
            ->type('percentage')
            ->value(10)
            ->data([
                'foo' => 'bar',
                'baz' => 'qux',
            ]);

        $create->save();

        $coupon = Coupon::findByCode('test');

        $this->assertTrue($create instanceof CouponsCoupon);

        $this->assertSame($coupon->id(), 'this-is-a-test-id');
        $this->assertSame($create->code(), 'test');
        $this->assertSame($create->type(), 'percentage');
        $this->assertSame($create->value(), 10);
        $this->assertSame($create->get('foo'), 'bar');
        $this->assertSame($create->get('baz'), 'qux');
    }

    /** @test */
    public function can_update()
    {
        $create = Coupon::make()
            ->id('this-is-a-test-id')
            ->code('test')
            ->type('percentage')
            ->value(10)
            ->data([
                'foo' => 'bar',
                'baz' => 'qux',
            ]);

        $create->save();

        $this->assertSame($create->value(), 10);
        $this->assertSame($create->get('foo'), 'bar');

        $create->value(20);
        $create->set('foo', 'baz');

        $create->save();

        $this->assertSame($create->id(), 'this-is-a-test-id');
        $this->assertSame($create->code(), 'test');
        $this->assertSame($create->type(), 'percentage');
        $this->assertSame($create->value(), 20);
        $this->assertSame($create->get('foo'), 'baz');
        $this->assertSame($create->get('baz'), 'qux');
    }

    /** @test */
    public function can_delete()
    {
        $create = Coupon::make()
            ->id('this-is-a-test-id')
            ->code('test')
            ->type('percentage')
            ->value(10)
            ->data([
                'foo' => 'bar',
                'baz' => 'qux',
            ]);

        $create->save();

        $this->assertFileExists($create->path());

        $create->delete();

        $this->assertFileDoesNotExist($create->path());
    }
}
