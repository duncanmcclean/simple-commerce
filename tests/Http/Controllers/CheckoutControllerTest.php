<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\PreCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Statamic\Facades\Stache;

class CheckoutControllerTest extends TestCase
{
    use SetupCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function can_post_checkout()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

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

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_name_and_email()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

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

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been created with provided details
        $this->assertNotNull($order->get('customer'));

        $this->assertSame($order->customer()->name(), 'Mike Scott');
        $this->assertSame($order->customer()->email(), 'mike.scott@example.com');

        $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
            $order->id,
        ]);

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_only_email()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

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

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Assert email has been set on the order
        $this->assertNull($order->get('customer'));
        $this->assertSame($order->get('email'), 'jim@example.com');

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_customer_already_present_in_order()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $customer = Customer::create([
            'name' => 'Dwight Schrute',
            'email' => 'dwight.schrute@example.com',
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
            'customer'    => $customer->id,
        ]);

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'gateway'      => DummyGateway::class,
                'card_number'  => '4242424242424242',
                'expiry_month' => '01',
                'expiry_year'  => '2025',
                'cvc'          => '123',
            ]);

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been updated
        $this->assertNotNull($order->get('customer'));
        $this->assertSame($order->get('customer'), $customer->id);

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

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $customer = Customer::create([
            'name' => 'Stanley Hudson',
            'email' => 'stanley.hudson@example.com',
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

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

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been updated
        $this->assertNotNull($order->get('customer'));
        $this->assertSame($order->get('customer'), $customer->id);

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
        //
    }

    /** @test */
    public function cant_post_checkout_with_coupon_where_minimum_cart_value_has_not_been_reached()
    {
        //
    }

    /** @test */
    public function cant_post_checkout_with_coupon_when_coupon_has_been_redeemed_for_maxium_uses()
    {
        //
    }

    /** @test */
    public function cant_post_checkout_with_coupon_where_coupon_is_only_valid_for_products_not_in_cart()
    {
        //
    }

    /** @test */
    public function can_post_checkout_with_product_with_stock_counter()
    {
        //
    }

    /** @test */
    public function can_post_checkout_when_product_is_running_low_on_stock()
    {
        //
    }

    /** @test */
    public function cant_post_checkout_when_product_has_no_stock()
    {
        //
    }

    /** @test */
    public function can_post_checkout_and_ensure_remaining_request_data_is_saved_to_order()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
            'gift_note' => 'I like jam on toast!',
            'delivery_note' => 'We live at the red house at the top of the hill.',
        ]);

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

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Assert that the 'extra remaining data' has been saved to the order
        $this->assertSame($order->get('gift_note'), 'I like jam on toast!');
        $this->assertSame($order->get('delivery_note'), 'We live at the red house at the top of the hill.');

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_with_extra_line_item_and_ensure_order_is_recalculated()
    {
        //
    }

    /** @test */
    public function can_post_checkout_with_no_payment_information_on_free_order()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Nothing',
            'price' => 0,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 0,
                ],
            ],
            'grand_total' => 0,
        ]);

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
            ]);

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_no_payment_information_on_paid_order()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => DummyGateway::class,
            ])
            ->assertSessionHasErrors(['card_number', 'expiry_month', 'expiry_year', 'cvc']);

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->published);

        $this->assertFalse($order->get('is_paid'));
        $this->assertNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_no_gateway_in_request()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
            ])
            ->assertSessionHasErrors('gateway');

        $order->fresh();

        // Assert events have been dispatched
        Event::assertNotDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->published);

        $this->assertFalse($order->get('is_paid'));
        $this->assertNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function cant_post_checkout_with_invalid_gateway_in_request()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

        $this
            ->withSession(['simple-commerce-cart' => $order->id])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name'         => 'Smelly Joe',
                'email'        => 'smelly.joe@example.com',
                'gateway'      => 'TripleFourDigital\\ComplexCommerce\\SmellyGatewayHaha',
            ])
            ->assertSessionHasErrors('gateway');

        $order->fresh();

        // Assert events have been dispatched
        Event::assertNotDispatched(PreCheckout::class);
        Event::assertNotDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertFalse($order->published);

        $this->assertFalse($order->get('is_paid'));
        $this->assertNull($order->get('paid_date'));

        // Finally, assert order is no longer attached to the users' session
        $this->assertTrue(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_requesting_json_and_ensure_json_is_returned()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

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

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been created with provided details
        $this->assertNotNull($order->get('customer'));

        $this->assertSame($order->customer()->name(), 'Smelly Joe');
        $this->assertSame($order->customer()->email(), 'smelly.joe@example.com');

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }

    /** @test */
    public function can_post_checkout_and_ensure_user_is_redirected()
    {
        Event::fake();

        $product = Product::create([
            'title' => 'Bacon',
            'price' => 5000,
        ]);

        $order = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 5000,
                ],
            ],
            'grand_total' => 5000,
        ]);

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
                '_redirect'    => '/order-confirmation',
            ])
            ->assertRedirect('/order-confirmation');

        $order->fresh();

        // Assert events have been dispatched
        Event::assertDispatched(PreCheckout::class);
        Event::assertDispatched(PostCheckout::class);

        // Assert order has been marked as paid
        $this->assertTrue($order->published);

        $this->assertTrue($order->get('is_paid'));
        $this->assertNotNull($order->get('paid_date'));

        // Assert customer has been created with provided details
        $this->assertNotNull($order->get('customer'));

        $this->assertSame($order->customer()->name(), 'Smelly Joe');
        $this->assertSame($order->customer()->email(), 'smelly.joe@example.com');

        // Finally, assert order is no longer attached to the users' session
        $this->assertFalse(session()->has('simple-commerce-cart'));
    }
}
