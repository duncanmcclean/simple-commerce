<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as ContractsOrder;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\PreCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunOut;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use DoubleThreeDigital\SimpleCommerce\Notifications\BackOfficeOrderPaid;
use DoubleThreeDigital\SimpleCommerce\Notifications\CustomerOrderPaid;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tests\RefreshContent;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Statamic\Facades\Stache;

class CheckoutControllerTest extends TestCase
{
    use SetupCollections, RefreshContent;

    public function setUp(): void
    {
        parent::setUp();

        $this->useBasicTaxEngine();
    }

    /** @test */
    public function can_post_checkout()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_and_ensure_custom_form_request_is_used()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                '_request'     => encrypt(CheckoutFormRequest::class),
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ])
            ->assertSessionHasErrors('accept_terms');

        $this->assertEquals(session('errors')->default->first('accept_terms'), 'Please accept the terms & conditions.');

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_name_and_email()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Mike Scott',
                'email'        => 'mike.scott@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been created with provided details
        $this->assertNotNull($order->customer());

        $this->assertSame($order->customer()->name(), 'Mike Scott');
        $this->assertSame($order->customer()->email(), 'mike.scott@example.com');

        $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
            $order->id,
        ]);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_first_name_and_last_name_and_email()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'first_name'   => 'Mike',
                'last_name'    => 'Scott',
                'email'        => 'mike.scott@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been created with provided details
        $this->assertNotNull($order->customer());

        $this->assertSame($order->customer()->name(), 'Mike Scott');
        $this->assertSame($order->customer()->email(), 'mike.scott@example.com');

        $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
            $order->id,
        ]);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_name_and_email_when_email_address_contains_spaces()
    {
        Event::fake();

        $product = Product::make()->price(5000)->data([
            'title' => 'Bacon',
        ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Mike Scott',
                'email'        => 'mike dot scott@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ])
            ->assertSessionHasErrors('email');

        $order->fresh();

        // Assert events have been dispatched
        Event::assertNotDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Assert customer has been created with provided details
        $this->assertNull($order->customer());

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_only_email()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'email'        => 'jim@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert email has been set on the order
        $this->assertNotNull($order->customer());
        $this->assertNull($order->get('email'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_customer_already_present_in_order()
    {
        $this->markTestSkipped();

        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $customer = Customer::make()->email('dwight.schrute@example.com')->data([
            'name' => 'Dwight Schrute',
        ]);

        $customer->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000)->merge([
            'customer'    => $customer->id,
        ]);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been updated
        $this->assertNotNull($order->customer());
        $this->assertSame($order->customer(), $customer->id);

        $this->assertSame($order->customer()->name(), 'Dwight Schrute');
        $this->assertSame($order->customer()->email(), 'dwight.schrute@example.com');

        $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
            $order->id,
        ]);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_customer_present_in_request()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $customer = Customer::make()->email('stanley.hudson@example.com')->data([
            'name' => 'Stanley Hudson',
        ]);

        $customer->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'customer'     => $customer->id,
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been updated
        $this->assertNotNull($order->customer());
        $this->assertSame($order->customer()->id(), $customer->id);

        $this->assertSame($order->customer()->name(), 'Stanley Hudson');
        $this->assertSame($order->customer()->email(), 'stanley.hudson@example.com');

        $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
            $order->id,
        ]);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_coupon()
    {
        $this->markTestSkipped();

        Config::set('simple-commerce.tax_engine_config.rate', 0);
        Config::set('simple-commerce.sites.default.shipping.methods', []);

        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $coupon = Coupon::make()
            ->code('fifty-friday')
            ->value(50)
            ->type('percentage')
            ->data([
                'title'              => 'Fifty Friday',
                'redeemed'           => 0,
                'minimum_cart_value' => null,
            ]);

        $coupon->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
                'coupon'       => 'fifty-friday',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert the coupon has been redeemed propery & the total has been recalculated
        $this->assertSame($order->coupon()->id(), $coupon->id);

        $this->assertSame($order->grandTotal(), 2500);
        $this->assertSame($order->couponTotal(), 2500);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_coupon_where_minimum_cart_value_has_not_been_reached()
    {
        $this->markTestSkipped();

        Config::set('simple-commerce.tax_engine_config.rate', 0);
        Config::set('simple-commerce.sites.default.shipping.methods', []);

        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $coupon = Coupon::make()
            ->code('fifty-thursday')
            ->value(50)
            ->type('percentage')
            ->data([
                'title'              => 'Fifty Thursday',
                'redeemed'           => 0,
                'minimum_cart_value' => 9000,
            ]);

        $coupon->save();

        $order = Order::make()
            ->lineItems([
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ])
            ->grandTotal(5000)
            ->itemsTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
                'coupon'       => $coupon->code(),
            ])
            ->assertSessionHasErrors('coupon');

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Assert the coupon has been redeemed propery & the total has been recalculated
        $this->assertNull($order->coupon());

        $this->assertSame($order->grandTotal(), 5000);
        $this->assertSame($order->couponTotal(), 0);

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_coupon_when_coupon_has_been_redeemed_for_maxium_uses()
    {
        Config::set('simple-commerce.tax_engine_config.rate', 0);
        Config::set('simple-commerce.sites.default.shipping.methods', []);

        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $coupon = Coupon::make()
            ->code('fifty-thursday')
            ->value(50)
            ->type('percentage')
            ->data([
                'title'              => 'Fifty Thursday',
                'redeemed'           => 10,
                'maximum_uses'       => 10,
                'minimum_cart_value' => null,
            ]);

        $coupon->save();

        $order = Order::make()
            ->lineItems([
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ])
            ->grandTotal(5000)
            ->itemsTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
                'coupon'       => $coupon->code(),
            ])
            ->assertSessionHasErrors('coupon');

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Assert the coupon has been redeemed propery & the total has been recalculated
        $this->assertNull($order->coupon());

        $this->assertSame($order->grandTotal(), 5000);
        $this->assertSame($order->couponTotal(), 0);

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_coupon_where_coupon_is_only_valid_for_products_not_in_cart()
    {
        Config::set('simple-commerce.tax_engine_config.rate', 0);
        Config::set('simple-commerce.sites.default.shipping.methods', []);

        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $coupon = Coupon::make()
            ->code('fifty-wednesday')
            ->value(50)
            ->type('percentage')
            ->data([
                'title'              => 'Fifty Wednesday',
                'redeemed'           => 0,
                'minimum_cart_value' => null,
                'products'           => ['a-random-product'],
            ]);

        $coupon->save();

        $order = Order::make()
            ->lineItems([
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ])
            ->grandTotal(5000)
            ->itemsTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
                'coupon'       => $coupon->code(),
            ])
            ->assertSessionHasErrors('coupon');

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Assert the coupon has been redeemed propery & the total has been recalculated
        $this->assertNull($order->coupon());

        $this->assertSame($order->grandTotal(), 5000);
        $this->assertSame($order->couponTotal(), 0);

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_product_with_stock_counter()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->stock(50)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert stock has been reduced
        $this->assertSame($product->fresh()->stock(), 49);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_when_product_is_running_low_on_stock()
    {
        Config::set('simple-commerce.low_stock_threshold', 10);

        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->stock(9)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert stock has been reduced
        $this->assertSame($product->fresh()->stock(), 8);

        Event::assertDispatched(StockRunningLow::class);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_when_product_has_no_stock()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->stock(0)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors();

        $order = $order->fresh();

        // Assert the line item has been wiped out
        $this->assertSame($order->lineItems()->count(), 0);
        $this->assertSame($order->grandTotal(), 0);

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Assert stock has been reduced
        $product->fresh();
        $this->assertSame($product->stock(), 0);

        Event::assertNotDispatched(StockRunningLow::class);
        Event::assertDispatched(StockRunOut::class);

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_when_product_has_a_single_item_left_in_stock_and_single_quantity_in_cart()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->stock(1)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ])
            ->assertRedirect();

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert stock has been reduced
        $this->assertSame($product->fresh()->stock(), 0);

        Event::assertDispatched(StockRunningLow::class);
        Event::assertNotDispatched(StockRunOut::class);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_variant_product_with_stock_counter()
    {
        Event::fake();

        $product = Product::make()
            ->data([
                'title' => 'Bacon',
            ])
            ->productVariants([
                'variants' => [
                    [
                        'name'   => 'Colours',
                        'values' => [
                            'Red',
                        ],
                    ],
                    [
                        'name'   => 'Sizes',
                        'values' => [
                            'Small',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key'     => 'Red_Small',
                        'variant' => 'Red Small',
                        'price'   => 5000,
                        'stock'   => 50,
                    ],
                ],
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'variant'  => 'Red_Small',
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $r = $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert stock has been reduced
        $this->assertSame($product->fresh()->variant('Red_Small')->stock(), 49);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_when_variant_product_is_running_low_on_stock()
    {
        Config::set('simple-commerce.low_stock_threshold', 10);

        Event::fake();

        $product = Product::make()
            ->data([
                'title' => 'Bacon',
            ])
            ->productVariants([
                'variants' => [
                    [
                        'name'   => 'Colours',
                        'values' => [
                            'Red',
                        ],
                    ],
                    [
                        'name'   => 'Sizes',
                        'values' => [
                            'Small',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key'     => 'Red_Small',
                        'variant' => 'Red Small',
                        'price'   => 5000,
                        'stock'   => 9,
                    ],
                ],
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'variant'  => 'Red_Small',
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert stock has been reduced
        $this->assertSame($product->fresh()->variant('Red_Small')->stock(), 8);

        Event::assertDispatched(StockRunningLow::class);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_when_variant_product_has_no_stock()
    {
        Event::fake();

        $product = Product::make()
            ->data([
                'title' => 'Bacon',
            ])
            ->productVariants([
                'variants' => [
                    [
                        'name'   => 'Colours',
                        'values' => [
                            'Red',
                        ],
                    ],
                    [
                        'name'   => 'Sizes',
                        'values' => [
                            'Small',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key'     => 'Red_Small',
                        'variant' => 'Red Small',
                        'price'   => 5000,
                        'stock'   => 0,
                    ],
                ],
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'variant'  => 'Red_Small',
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors();

        $order = $order->fresh();

        // Assert the line item has been wiped out
        $this->assertSame($order->lineItems()->count(), 0);
        $this->assertSame($order->grandTotal(), 0);

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Assert stock has been reduced
        $this->assertSame($product->fresh()->variant('Red_Small')->stock(), 0);

        Event::assertNotDispatched(StockRunningLow::class);
        Event::assertDispatched(StockRunOut::class);

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_when_variant_product_has_a_single_item_left_in_stock_and_single_quantity_in_cart()
    {
        Config::set('simple-commerce.low_stock_threshold', 10);

        Event::fake();

        $product = Product::make()
            ->data([
                'title' => 'Bacon',
            ])
            ->productVariants([
                'variants' => [
                    [
                        'name'   => 'Colours',
                        'values' => [
                            'Red',
                        ],
                    ],
                    [
                        'name'   => 'Sizes',
                        'values' => [
                            'Small',
                        ],
                    ],
                ],
                'options' => [
                    [
                        'key'     => 'Red_Small',
                        'variant' => 'Red Small',
                        'price'   => 5000,
                        'stock'   => 1,
                    ],
                ],
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'variant'  => 'Red_Small',
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert stock has been reduced
        $this->assertSame($product->fresh()->variant('Red_Small')->stock(), 0);

        Event::assertDispatched(StockRunningLow::class);
        Event::assertNotDispatched(StockRunOut::class);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_and_ensure_remaining_request_data_is_saved_to_order()
    {
        Event::fake();

        Config::set('simple-commerce.field_whitelist.orders', ['the_extra']);

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000)->merge([
            'gift_note' => 'I like jam on toast!',
            'delivery_note' => 'We live at the red house at the top of the hill.',
        ]);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
                'the_extra'    => 'bit_of_data',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert that the 'extra remaining data' has been saved to the order
        $this->assertSame($order->get('gift_note'), 'I like jam on toast!');
        $this->assertSame($order->get('delivery_note'), 'We live at the red house at the top of the hill.');

        $this->assertSame($order->get('the_extra'), 'bit_of_data');

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_and_ensure_remaining_request_data_is_saved_to_order_if_fields_not_whitelisted_in_config()
    {
        Event::fake();

        Config::set('simple-commerce.field_whitelist.orders', []);

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000)->merge([
            'gift_note' => 'I like jam on toast!',
            'delivery_note' => 'We live at the red house at the top of the hill.',
        ]);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
                'the_extra'    => 'bit_of_data',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert that the 'extra remaining data' has been saved to the order
        $this->assertSame($order->get('gift_note'), 'I like jam on toast!');
        $this->assertSame($order->get('delivery_note'), 'We live at the red house at the top of the hill.');

        $this->assertNull($order->get('the_extra'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_extra_line_item_and_ensure_order_is_recalculated()
    {
        Config::set('simple-commerce.tax_engine_config.rate', 0);
        Config::set('simple-commerce.sites.default.shipping.methods', []);

        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make();
        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
                'items'        => [
                    [
                        'id'       => Stache::generateId(),
                        'product'  => $product->id,
                        'quantity' => 1,
                        'total'    => 5000,
                    ],
                ],
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert totals are calculated
        $this->assertSame($order->itemsTotal(), 5000);
        $this->assertSame($order->grandTotal(), 5000);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_no_payment_information_on_free_order()
    {
        Event::fake();

        $product = Product::make()
            ->price(0)
            ->data([
                'title' => 'Nothing',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 0,
            ],
        ])->grandTotal(0);

        $order->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(OrderPaid::class);
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_no_payment_information_on_paid_order()
    {
        $this->markTestIncomplete();

        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
            ])
            ->assertSessionHasErrors(['card_number', 'expiry_month', 'expiry_year', 'cvc']);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_no_gateway_in_request()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
            ])
            ->assertSessionHasErrors('gateway');

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertNotDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_invalid_gateway_in_request()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => 'TripleFourDigital\\ComplexCommerce\\SmellyGatewayHaha',
            ])
            ->assertSessionHasErrors('gateway');

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertNotDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_requesting_json_and_ensure_json_is_returned()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->postJson(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ])
            ->assertJsonStructure([
                'message',
                'cart',
                'status',
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been created with provided details
        $this->assertNotNull($order->customer());

        $this->assertSame($order->customer()->name(), 'Smelly Joe');
        $this->assertSame($order->customer()->email(), 'smelly.joe@example.com');

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_and_ensure_user_is_redirected()
    {
        Event::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
                '_redirect'    => encrypt('/order-confirmation'),
            ])
            ->assertRedirect('/order-confirmation');

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been created with provided details
        $this->assertNotNull($order->customer());

        $this->assertSame($order->customer()->name(), 'Smelly Joe');
        $this->assertSame($order->customer()->email(), 'smelly.joe@example.com');

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_and_ensure_order_paid_notifications_are_sent()
    {
        Notification::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Guvna B',
                'email'        => 'guvna.b@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Asset notifications have been sent
        Notification::assertSentTo(
            (new AnonymousNotifiable())->route('mail', 'guvna.b@example.com'),
            CustomerOrderPaid::class
        );

        Notification::assertSentTo(
            (new AnonymousNotifiable())->route('mail', 'duncan@example.com'),
            BackOfficeOrderPaid::class
        );

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_and_ensure_temp_gateway_data_is_tidied_up()
    {
        Notification::fake();

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000)->merge([
            'dummy' => [
                'foo' => 'bar',
            ],
        ]);

        $order->save();

        // Double check 'dummy' temp data is actually present
        $this->assertIsArray($order->get('dummy'));

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order = $order->fresh();

        // Assert order has been marked as paid
        $this->assertTrue($order->get('published'));

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->get('paid_date'));

        // Assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));

        // Finally, assert 'dummy' gateway temp data has been tiedied up
        $this->assertNull($order->get('dummy'));
    }

    /** @test */
    public function can_post_checkout_and_ensure_gateway_validation_rules_are_used()
    {
        Event::fake();

        SimpleCommerce::registerGateway(TestValidationGateway::class);

        $product = Product::make()
            ->price(5000)
            ->data([
                'title' => 'Bacon',
            ]);

        $product->save();

        $order = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 5000,
            ],
        ])->grandTotal(5000);

        $order->save();

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->postJson(route('statamic.simple-commerce.checkout.store'), [
                'gateway' => TestValidationGateway::class,
            ])
            ->assertJson([
                'errors' => [
                    'something_mental' => [
                        'You must have something mental to do.',
                    ],
                ],
            ]);

        $order = $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->get('published'));

        $this->assertFalse($order->isPaid());
        $this->assertNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }
}

class CheckoutFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'accept_terms' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'accept_terms.required' => 'Please accept the terms & conditions.',
        ];
    }
}

class TestValidationGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Test Validation Gateway';
    }

    public function prepare(Prepare $data): Response
    {
        return new Response(true, [
            'bagpipes' => 'music',
        ], 'http://backpipes.com');
    }

    public function purchase(Purchase $data): Response
    {
        return new Response(true);
    }

    public function purchaseRules(): array
    {
        return [
            'something_mental' => ['required'],
        ];
    }

    public function purchaseMessages(): array
    {
        return [
            'something_mental.required' => 'You must have something mental to do.',
        ];
    }

    public function getCharge(ContractsOrder $order): Response
    {
        return new Response(true, []);
    }

    public function refundCharge(ContractsOrder $order): Response
    {
        return new Response(true, []);
    }

    public function webhook(Request $request)
    {
        return 'Success.';
    }
}
