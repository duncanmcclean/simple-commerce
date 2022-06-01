<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class CouponControllerTest extends TestCase
{
    use SetupCollections;

    public $product;
    public $cart;

    public function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(base_path('content/collections/coupons'));

        $this->useBasicTaxEngine();
    }

    /** @test */
    public function can_store_coupon()
    {
        Event::fake();

        $this->buildCartWithProducts();

        $coupon = Entry::make()
            ->collection('coupons')
            ->slug('hof-price')
            ->data([
                'title'              => 'Half Price',
                'redeemed'           => 0,
                'coupon_value'       => 50,
                'type'               => 'percentage',
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

        $coupon = Entry::make()
            ->collection('coupons')
            ->slug('halav-price')
            ->data([
                'title'              => 'Half Price',
                'redeemed'           => 0,
                'coupon_value'       => 50,
                'type'               => 'percentage',
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

        $coupon = Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('half-price')
            ->data([
                'title'              => 'Half Price',
                'redeemed'           => 5,
                'coupon_value'       => 50,
                'type'               => 'percentage',
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
    public function can_store_coupon_limited_to_certain_products_when_product_is_in_cart()
    {
        Event::fake();

        $this->buildCartWithProducts();

        $coupon = Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('half-price')
            ->data([
                'title'              => 'Half Price',
                'redeemed'           => 0,
                'coupon_value'       => 50,
                'type'               => 'percentage',
                'minimum_cart_value' => null,
                'products'           => [$this->product->id],
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
        $response->assertSessionHasNoErrors();

        $this->cart = $this->cart->fresh();

        $this->assertSame($this->cart->coupon()->id(), $coupon->id());
        $this->assertNotSame($this->cart->couponTotal(), 0000);

        Event::assertDispatched(CouponRedeemed::class);
    }

    /** @test */
    public function cant_store_coupon_limited_to_certain_products_when_products_are_not_in_the_cart()
    {
        $this->buildCartWithProducts();

        $coupon = Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('half-price')
            ->data([
                'title'              => 'Half Price',
                'redeemed'           => 5,
                'coupon_value'       => 50,
                'type'               => 'percentage',
                'minimum_cart_value' => null,
                'maximum_uses'       => 0,
                'products'           => ['another-product-id'],
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
    public function can_store_coupon_limited_to_certain_customers_and_current_customer_is_in_allow_list()
    {
        Event::fake();

        $this->buildCartWithProducts();

        $customer = Customer::make()
            ->email('john@doe.com')
            ->data([
                'name' => 'John Doe',
            ]);

        $customer->save();

        $this->cart->customer($customer->id());
        $this->cart->save();

        $coupon = Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('hof-price')
            ->data([
                'title'              => 'Hof Price',
                'redeemed'           => 0,
                'coupon_value'       => 50,
                'type'               => 'percentage',
                'minimum_cart_value' => null,
                'customers'          => [$customer->id],
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
        $response->assertSessionHasNoErrors();

        $this->cart = $this->cart->fresh();

        $this->assertSame($this->cart->coupon()->id(), $coupon->id());
        $this->assertNotSame($this->cart->couponTotal(), 0000);

        Event::assertDispatched(CouponRedeemed::class);
    }

    /** @test */
    public function cant_store_coupon_limited_to_certain_customers_and_current_customer_is_not_in_allow_list()
    {
        $this->buildCartWithProducts();

        $customer = Customer::make()
            ->email('john@doe.com')
            ->data([
                'name' => 'John Doe',
            ]);

        $customer->save();

        $this->cart->customer(null);
        $this->cart->save();

        $coupon = Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('halv-price')
            ->data([
                'title'              => 'Halv Price',
                'redeemed'           => 0,
                'coupon_value'       => 50,
                'type'               => 'percentage',
                'minimum_cart_value' => null,
                'customers'          => [$customer->id],
            ]);

        $coupon->save();
        $coupon->fresh();

        $data = [
            'code' => 'halv-price',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $this->cart->id])
            ->post(route('statamic.simple-commerce.coupon.store'), $data);

        $response->assertRedirect('/cart');
        $response->assertSessionHasErrors();

        $this->cart->fresh();

        $this->assertNull($this->cart->coupon());
        $this->assertSame($this->cart->couponTotal(), 0000);
    }

    /** @test */
    public function can_destroy_coupon()
    {
        $this->buildCartWithProducts();

        $coupon = Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('half-price')
            ->data([
                'title'              => 'Half Price',
                'redeemed'           => 0,
                'coupon_value'       => 50,
                'type'               => 'percentage',
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

        $coupon = Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('half-price')
            ->data([
                'title'              => 'Half Price',
                'redeemed'           => 0,
                'coupon_value'       => 50,
                'type'               => 'percentage',
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
