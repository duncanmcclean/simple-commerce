<?php

use DuncanMcClean\SimpleCommerce\Events\CouponRedeemed;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
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

    [$product, $cart] = buildCartWithProducts();

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
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.coupon.store'), $data);

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    expect($coupon->id())->toBe($cart->coupon()->id());
    $this->assertNotSame($cart->couponTotal(), 0);

    Event::assertDispatched(function (CouponRedeemed $event) use ($cart) {
        return $event->coupon->id() === $cart->coupon()->id()
            && $event->order->id() === $cart->id();
    });
});

test('can store coupon and request json response', function () {
    Event::fake();

    [$product, $cart] = buildCartWithProducts();

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
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->postJson(route('statamic.simple-commerce.coupon.store'), $data);

    $response->assertJsonStructure([
        'status',
        'message',
        'cart',
    ]);

    $cart = $cart->fresh();

    expect($coupon->id())->toBe($cart->coupon()->id());
    $this->assertNotSame($cart->couponTotal(), 0000);

    Event::assertDispatched(CouponRedeemed::class);
});

test('cant store invalid coupon', function () {
    [$product, $cart] = buildCartWithProducts();

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
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.coupon.store'), $data);

    $response->assertRedirect('/cart');
    $response->assertSessionHasErrors();

    $cart = $cart->fresh();

    expect($cart->coupon())->toBeNull();
    expect(00)->toBe($cart->couponTotal());
});

test('cant store coupon that does not exist', function () {
    [$product, $cart] = [$product, $cart] = buildCartWithProducts();

    $data = [
        'code' => 'christmas',
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.coupon.store'), $data);

    $response->assertRedirect('/cart');
    $response->assertSessionHasErrors();

    $cart = $cart->fresh();

    $this->assertNull($cart->coupon(), 0000);
});

test('can destroy coupon', function () {
    [$product, $cart] = buildCartWithProducts();

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

    $cart->coupon($coupon->id());
    $cart->save();

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->delete(route('statamic.simple-commerce.coupon.destroy'));

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    expect($cart->coupon())->toBeNull();
    expect(00)->toBe($cart->couponTotal());
});

test('can destroy coupon and request json', function () {
    [$product, $cart] = buildCartWithProducts();

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

    $cart->coupon($coupon->id());
    $cart->save();

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id()])
        ->deleteJson(route('statamic.simple-commerce.coupon.destroy'));

    $response->assertJsonStructure([
        'status',
        'message',
        'cart',
    ]);

    $cart = $cart->fresh();

    expect($cart->coupon())->toBeNull();
    expect(00)->toBe($cart->couponTotal());
});
