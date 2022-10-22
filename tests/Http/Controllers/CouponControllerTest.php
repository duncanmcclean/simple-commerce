<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Stache;

class CouponControllerTest extends TestCase
{
    use SetupCollections;

    public $product;
    public $cart;

    public function setUp(): void
    {
        parent::setUp();

        Stache::store('simple-commerce-coupons')->clear();

        collect(File::allFiles(base_path('content/simple-commerce/coupons')))
            ->each(function ($file) {
                File::delete($file);
            });

        $this->useBasicTaxEngine();
    }

    /** @test */
    public function can_store_coupon()
    {
        Event::fake();

        $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->code('hof-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Half Price',
                'redeemed'           => 0,
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

        $this->assertSame($this->cart->coupon()->id(), $coupon->id());
        $this->assertNotSame($this->cart->couponTotal(), 0);

        Event::assertDispatched(CouponRedeemed::class);
    }

    /** @test */
    public function can_store_coupon_and_request_json_response()
    {
        Event::fake();

        $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->code('halav-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Half Price',
                'redeemed'           => 0,
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

        $this->assertSame($this->cart->coupon()->id(), $coupon->id());
        $this->assertNotSame($this->cart->couponTotal(), 0000);

        Event::assertDispatched(CouponRedeemed::class);
    }

    /** @test */
    public function cant_store_invalid_coupon()
    {
        $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->code('half-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Half Price',
                'redeemed'           => 5,
                'minimum_cart_value' => null,
                'maximum_uses'       => 5, // We shouldn't be able to use because of this
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

        $this->assertNull($this->cart->coupon());
        $this->assertSame($this->cart->couponTotal(), 0000);
    }

    /** @test */
    public function cant_store_coupon_that_does_not_exist()
    {
        $this->buildCartWithProducts();

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
    }

    /** @test */
    public function can_destroy_coupon()
    {
        $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->code('half-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Half Price',
                'redeemed'           => 0,
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

        $this->assertNull($this->cart->coupon());
        $this->assertSame($this->cart->couponTotal(), 0000);
    }

    /** @test */
    public function can_destroy_coupon_and_request_json()
    {
        $this->buildCartWithProducts();

        $coupon = Coupon::make()
            ->code('half-price')
            ->value(50)
            ->type('percentage')
            ->data([
                'description'        => 'Half Price',
                'redeemed'           => 0,
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

        $this->assertNull($this->cart->coupon());
        $this->assertSame($this->cart->couponTotal(), 0000);
    }

    protected function buildCartWithProducts()
    {
        $this->product = Product::make()
            ->price(1000)
            ->data([
                'title' => 'Food',
            ]);

        $this->product->save();

        $this->cart = Order::make()
            ->lineItems([
                [
                    'id'       => Stache::generateId(),
                    'product'  => $this->product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ]);

        $this->cart->save();
    }
}
