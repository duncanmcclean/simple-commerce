<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class CouponControllerTest extends TestCase
{
    use CollectionSetup;

    public $product;
    public $cart;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function can_store_coupon()
    {
        Event::fake();

        $this->buildCartWithProducts();

        Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('half-price')
            ->data([
                'title' => 'Half Price',
                'redeemed' => 0,
                'value' => 50,
                'type' => 'percentage',
                'minimum_cart_value' => null,
            ])
            ->save();
        $coupon = Entry::findBySlug('half-price', 'coupons');

        $data = [
            'code' => 'half-price',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $this->cart->id])
            ->post(route('statamic.simple-commerce.coupon.store'), $data);

        $response->assertRedirect('/cart');

        $this->cart->find($this->cart->id);

        $this->assertSame($this->cart->data['coupon'], $coupon->id());
        $this->assertNotSame($this->cart->data['coupon_total'], 0000);

        Event::assertDispatched(CouponRedeemed::class);
    }

    /** @test */
    public function cant_store_invalid_coupon()
    {
        $this->buildCartWithProducts();

        Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('half-price')
            ->data([
                'title' => 'Half Price',
                'redeemed' => 5,
                'value' => 50,
                'type' => 'percentage',
                'minimum_cart_value' => null,
                'maximum_uses' => 5, // We shouldn't be able to use because of this
            ])
            ->save();
        $coupon = Entry::findBySlug('half-price', 'coupons');

        $data = [
            'code' => 'half-price',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $this->cart->id])
            ->post(route('statamic.simple-commerce.coupon.store'), $data);

        $response->assertRedirect('/cart');
        $response->assertSessionHasErrors();

        $this->cart->find($this->cart->id);

        $this->assertNotSame($this->cart->data['coupon'], $coupon->id());
        $this->assertSame($this->cart->data['coupon_total'], 0000);
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

        $this->cart->find($this->cart->id);

        $this->assertNull($this->cart->data['coupon']);
        $this->assertSame($this->cart->data['coupon_total'], 0000);
    }

    /** @test */
    public function can_destroy_coupon()
    {
        $this->buildCartWithProducts();

        Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->slug('half-price')
            ->data([
                'title' => 'Half Price',
                'redeemed' => 0,
                'value' => 50,
                'type' => 'percentage',
                'minimum_cart_value' => null,
            ])
            ->save();
        $coupon = Entry::findBySlug('half-price', 'coupons');

        $this->cart->update([
            'coupon' => $coupon->id(),
        ]);

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $this->cart->id])
            ->delete(route('statamic.simple-commerce.coupon.destroy'));

        $response->assertRedirect('/cart');

        $this->cart->find($this->cart->id);

        $this->assertNull($this->cart->data['coupon']);
        $this->assertSame($this->cart->data['coupon_total'], 0000);
    }

    protected function buildCartWithProducts()
    {
        $this->product = Product::create([
            'title' => Food,
            'price' => 1000,
        ]);

        $this->cart = Order::create([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $this->product->id,
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
            'coupon' => null,
        ]);
    }
}
