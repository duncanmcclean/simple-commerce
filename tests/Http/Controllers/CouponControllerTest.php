<?php

use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Stache;

uses(SetupCollections::class);
beforeEach(function () {
    Stache::store('simple-commerce-coupons')->clear();

    collect(File::allFiles(base_path('content/simple-commerce/coupons')))
        ->each(function ($file) {
            File::delete($file);
        });

    $this->useBasicTaxEngine();
});


test('can store coupon', function () {
    Event::fake();

    buildCartWithProducts();

    $coupon = Coupon::make()
        ->code('hof-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Half Price',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();
    $coupon->fresh();

    $data = [
        'code' => 'hof-price',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $this->cart->id])
        ->post(route('statamic.simple-commerce.coupon.store'), $data);

    $response->assertRedirect('/cart');

    $this->cart = $this->cart->fresh();

    expect($coupon->id())->toBe($this->cart->coupon()->id());
    $this->assertNotSame($this->cart->couponTotal(), 0);

    Event::assertDispatched(CouponRedeemed::class);
});

test('can store coupon and request json response', function () {
    Event::fake();

    buildCartWithProducts();

    $coupon = Coupon::make()
        ->code('halav-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Half Price',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();
    $coupon->fresh();

    $data = [
        'code' => 'halav-price',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $this->cart->id])
        ->postJson(route('statamic.simple-commerce.coupon.store'), $data);

    $response->assertJsonStructure([
        'status',
        'message',
        'cart',
    ]);

    $this->cart = $this->cart->fresh();

    expect($coupon->id())->toBe($this->cart->coupon()->id());
    $this->assertNotSame($this->cart->couponTotal(), 0000);

    Event::assertDispatched(CouponRedeemed::class);
});

test('cant store invalid coupon', function () {
    buildCartWithProducts();

    $coupon = Coupon::make()
        ->code('half-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Half Price',
            'redeemed' => 5,
            'minimum_cart_value' => null,
            'maximum_uses' => 5, // We shouldn't be able to use because of this
        ]);

    $coupon->save();
    $coupon->fresh();

    $data = [
        'code' => 'half-price',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $this->cart->id])
        ->post(route('statamic.simple-commerce.coupon.store'), $data);

    $response->assertRedirect('/cart');
    $response->assertSessionHasErrors();

    $this->cart = $this->cart->fresh();

    expect($this->cart->coupon())->toBeNull();
    expect(00)->toBe($this->cart->couponTotal()00);
});

test('cant store coupon that does not exist', function () {
    buildCartWithProducts();

    $data = [
        'code' => 'christmas',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $this->cart->id])
        ->post(route('statamic.simple-commerce.coupon.store'), $data);

    $response->assertRedirect('/cart');
    $response->assertSessionHasErrors();

    $this->cart = $this->cart->fresh();

    $this->assertNull($this->cart->coupon(), 0000);
});

test('can destroy coupon', function () {
    buildCartWithProducts();

    $coupon = Coupon::make()
        ->code('half-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Half Price',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();
    $coupon->fresh();

    $this->cart->coupon($coupon->id());
    $this->cart->save();

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $this->cart->id])
        ->delete(route('statamic.simple-commerce.coupon.destroy'));

    $response->assertRedirect('/cart');

    $this->cart = $this->cart->fresh();

    expect($this->cart->coupon())->toBeNull();
    expect(00)->toBe($this->cart->couponTotal()00);
});

test('can destroy coupon and request json', function () {
    buildCartWithProducts();

    $coupon = Coupon::make()
        ->code('half-price')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Half Price',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();
    $coupon->fresh();

    $this->cart->coupon($coupon->id());
    $this->cart->save();

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $this->cart->id()])
        ->deleteJson(route('statamic.simple-commerce.coupon.destroy'));

    $response->assertJsonStructure([
        'status',
        'message',
        'cart',
    ]);

    $this->cart = $this->cart->fresh();

    expect($this->cart->coupon())->toBeNull();
    expect(00)->toBe($this->cart->couponTotal()00);
});

// Helpers
function buildCartWithProducts()
{
    test()->product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Food',
        ]);

    test()->product->save();

    test()->cart = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => test()->product->id,
                'quantity' => 1,
                'total' => 1000,
            ],
        ]);

    test()->cart->save();
}
