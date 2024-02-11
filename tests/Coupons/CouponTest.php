<?php

use DuncanMcClean\SimpleCommerce\Coupons\Coupon as CouponsCoupon;
use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use Statamic\Facades\Stache;

test('can create', function () {
    $create = Coupon::make()
        ->code('test')
        ->type('percentage')
        ->value(10)
        ->data([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

    $create->save();

    expect($create instanceof CouponsCoupon)->toBeTrue();

    $this->assertNotNull($create->id());

    expect('test')->toBe($create->code());
    expect(CouponType::Percentage)->toBe($create->type());
    expect(10)->toBe($create->value());
    expect('bar')->toBe($create->get('foo'));
    expect('qux')->toBe($create->get('baz'));
});

test('can find by id', function () {
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

    expect($create instanceof CouponsCoupon)->toBeTrue();

    expect('this-is-a-test-id')->toBe($coupon->id());
    expect('test')->toBe($create->code());
    expect(CouponType::Percentage)->toBe($create->type());
    expect(10)->toBe($create->value());
    expect('bar')->toBe($create->get('foo'));
    expect('qux')->toBe($create->get('baz'));
});

test('can find by code', function () {
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

    expect($create instanceof CouponsCoupon)->toBeTrue();

    expect('this-is-a-test-id')->toBe($coupon->id());
    expect('test')->toBe($create->code());
    expect(CouponType::Percentage)->toBe($create->type());
    expect(10)->toBe($create->value());
    expect('bar')->toBe($create->get('foo'));
    expect('qux')->toBe($create->get('baz'));
});

test('can update', function () {
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

    expect(10)->toBe($create->value());
    expect('bar')->toBe($create->get('foo'));

    $create->value(20);
    $create->set('foo', 'baz');

    $create->save();

    expect('this-is-a-test-id')->toBe($create->id());
    expect('test')->toBe($create->code());
    expect(CouponType::Percentage)->toBe($create->type());
    expect(20)->toBe($create->value());
    expect('baz')->toBe($create->get('foo'));
    expect('qux')->toBe($create->get('baz'));
});

test('can delete', function () {
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

    expect($create->path())->toBeFile();

    $create->delete();

    $this->assertFileDoesNotExist($create->path());
});

test('is valid when limited to certain products when product is in cart', function () {
    [$product, $order] = buildCartWithProducts();

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

    expect($isValid)->toBeTrue();
});

test('is not valid when limited to certain products when products are not in the cart', function () {
    [$product, $order] = buildCartWithProducts();

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

    expect($isValid)->toBeFalse();
});

test('is valid when limited to certain customers and current customer is in allow list', function () {
    [$product, $order] = buildCartWithProducts();

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
            'customer_eligibility' => 'specific_customers',
            'customers' => [$customer->id],
        ]);

    $coupon->save();

    $isValid = $coupon->isValid($order);

    expect($isValid)->toBeTrue();
});

test('is not valid when limited to customers and current customer is not in allow list', function () {
    [$product, $order] = buildCartWithProducts();

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
            'customer_eligibility' => 'specific_customers',
            'customers' => [$customer->id],
        ]);

    $coupon->save();

    $isValid = $coupon->isValid($order);

    expect($isValid)->toBeFalse();
});

test('is not valid before coupon valid_from timestamp', function () {
    [$product, $order] = buildCartWithProducts();

    $coupon = Coupon::make()
        ->id(Stache::generateId())
        ->code('halv-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Halv Price',
            'redeemed' => 0,
            'minimum_cart_value' => null,
            'valid_from' => '2030-01-01',
        ]);

    $coupon->save();

    $isValid = $coupon->isValid($order);

    expect($isValid)->toBeFalse();
});

test('is not valid after coupon has expired', function () {
    [$product, $order] = buildCartWithProducts();

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

    expect($isValid)->toBeFalse();
});

test('is valid for customer where email matches domain', function () {
    [$product, $order] = buildCartWithProducts();

    $customer = Customer::make()
        ->email('john@example.com')
        ->data(['name' => 'John Doe'])
        ->save();

    $order->customer($customer->id());
    $order->save();

    $coupon = Coupon::make()
        ->id(Stache::generateId())
        ->code('halv-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Halv Price',
            'redeemed' => 0,
            'minimum_cart_value' => null,
            'customer_eligibility' => 'customers_by_domain',
            'customers_by_domain' => [
                'example.com',
            ],
        ]);

    $coupon->save();

    $isValid = $coupon->isValid($order);

    expect($isValid)->toBeTrue();
});

test('is not valid for customer where email does not match domain', function () {
    [$product, $order] = buildCartWithProducts();

    $customer = Customer::make()
        ->email('john@example.com')
        ->data(['name' => 'John Doe'])
        ->save();

    $order->customer($customer->id());
    $order->save();

    $coupon = Coupon::make()
        ->id(Stache::generateId())
        ->code('halv-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Halv Price',
            'redeemed' => 0,
            'minimum_cart_value' => null,
            'customer_eligibility' => 'customers_by_domain',
            'customers_by_domain' => [
                'doublethree.digital',
            ],
        ]);

    $coupon->save();

    $isValid = $coupon->isValid($order);

    expect($isValid)->toBeFalse();
});
