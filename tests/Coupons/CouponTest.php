<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Coupons;

use DoubleThreeDigital\SimpleCommerce\Coupons\Coupon as CouponsCoupon;
use DoubleThreeDigital\SimpleCommerce\Coupons\CouponType;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Stache;

class CouponTest extends TestCase
{
    use SetupCollections;

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
        $this->assertSame($create->type(), CouponType::Percentage);
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
        $this->assertSame($create->type(), CouponType::Percentage);
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
        $this->assertSame($create->type(), CouponType::Percentage);
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
        $this->assertSame($create->type(), CouponType::Percentage);
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

    /** @test */
    public function is_valid_when_limited_to_certain_products_when_product_is_in_cart()
    {
        [$product, $order] = $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->code('half-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description' => 'Half Price',
                'redeemed' => 0,
                'minimum_cart_value' => null,
                'products' => [$product->id],
            ]);

        $coupon->save();

        $isValid = $coupon->isValid($order);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function is_not_valid_when_limited_to_certain_products_when_products_are_not_in_the_cart()
    {
        [$product, $order] = $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->id(Stache::generateId())
            ->code('half-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description' => 'Half Price',
                'redeemed' => 5,
                'minimum_cart_value' => null,
                'maximum_uses' => 0,
                'products' => ['another-product-id'],
            ]);

        $coupon->save();

        $isValid = $coupon->isValid($order);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function is_valid_when_limited_to_certain_customers_and_current_customer_is_in_allow_list()
    {
        [$product, $order] = $this->buildCartWithProducts();

        $customer = Customer::make()
            ->email('john@doe.com')
            ->data([
                'name' => 'John Doe',
            ]);

        $customer->save();

        $order->customer($customer->id());
        $order->save();

        $coupon = Coupon::make()
            ->id(Stache::generateId())
            ->code('hof-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description' => 'Hof Price',
                'redeemed' => 0,
                'minimum_cart_value' => null,
                'customers' => [$customer->id],
            ]);

        $coupon->save();

        $isValid = $coupon->isValid($order);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function is_not_valid_when_limited_to_customers_and_current_customer_is_not_in_allow_list()
    {
        [$product, $order] = $this->buildCartWithProducts();

        $customer = Customer::make()
            ->email('john@doe.com')
            ->data([
                'name' => 'John Doe',
            ]);

        $customer->save();

        $order->customer(null);
        $order->save();

        $coupon = Coupon::make()
            ->id(Stache::generateId())
            ->code('hof-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description' => 'Hof Price',
                'redeemed' => 0,
                'minimum_cart_value' => null,
                'customers' => [$customer->id],
            ]);

        $coupon->save();

        $isValid = $coupon->isValid($order);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function is_not_valid_when_coupon_is_disabled()
    {
        [$product, $order] = $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->id(Stache::generateId())
            ->code('halv-price')
            ->value(50)
            ->type('percentage')
            ->enabled(false)
            ->data([
                'description' => 'Halv Price',
                'redeemed' => 0,
                'minimum_cart_value' => null,
            ]);

        $coupon->save();

        $isValid = $coupon->isValid($order);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function is_not_valid_after_coupon_has_expired()
    {
        [$product, $order] = $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->id(Stache::generateId())
            ->code('halv-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description' => 'Halv Price',
                'redeemed' => 0,
                'minimum_cart_value' => null,
                'expires_at' => '2022-01-01',
            ]);

        $coupon->save();

        $isValid = $coupon->isValid($order);

        $this->assertFalse($isValid);
    }

    protected function buildCartWithProducts()
    {
        $product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Food',
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ]);

        $order->save();

        return [$product, $order];
    }
}
