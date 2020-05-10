<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Actions;

use App\User;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class CheckoutControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        factory(Currency::class)->create();
        factory(OrderStatus::class)->create(['primary' => true]);
    }

    /** @test */
    public function can_store_checkout_as_guest_with_no_history()
    {
        Event::fake();

        $order = factory(Order::class)->create();
        $lineItems = factory(LineItem::class, 2)->create(['order_id' => $order->id, 'quantity' => 1]);

        $this
            ->session(['simple_commerce_cart' => $order->uuid])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name' => 'George Murray',
                'email' => 'george@murray.com',

                'gateway' => 'DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway',
                'cardholder' => 'Mr George Murray',
                'cardNumber' => '4242 4242 4242 4242',
                'expiryMonth' => '01',
                'expiryYear' => '2025',
                'cvc' => '123',
            ])
            ->assertRedirect();

        $customer = User::where('email', 'george@murray.com')->first(); 

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
        ]);
        
        Event::assertDispatched(OrderPaid::class);
        Event::assertDispatched(OrderSuccessful::class);
    }

    /** @test */
    public function can_store_checkout_as_guest_with_history()
    {
        Event::fake();

        $customer = factory(User::class)->create([
            'name' => 'Tom Jackson',
            'email' => 'tom@jackson.com',
        ]);
        $order = factory(Order::class)->create();
        $lineItems = factory(LineItem::class, 2)->create(['order_id' => $order->id, 'quantity' => 1]);

        $this
            ->session(['simple_commerce_cart' => $order->uuid])
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name' => $customer->name,
                'email' => $customer->email,

                'gateway' => 'DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway',
                'cardholder' => 'Mr George Murray',
                'cardNumber' => '4242 4242 4242 4242',
                'expiryMonth' => '01',
                'expiryYear' => '2025',
                'cvc' => '123',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
        ]);

        Event::assertDispatched(OrderPaid::class);
        Event::assertDispatched(OrderSuccessful::class);
    }

    /** @test */
    public function can_store_checkout_as_logged_in_user()
    {
        Event::fake();

        $customer = factory(User::class)->create([
            'name' => 'Jack Thomson',
            'email' => 'jack@thomson.com',
        ]);
        $order = factory(Order::class)->create();
        $lineItems = factory(LineItem::class, 2)->create(['order_id' => $order->id, 'quantity' => 1]);

        $this
            ->session(['simple_commerce_cart' => $order->uuid])
            ->actingAs($customer)
            ->post(route('statamic.simple-commerce.checkout.store'), [
                'name' => $customer->name,
                'email' => $customer->email,

                'gateway' => 'DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway',
                'cardholder' => 'Mr George Murray',
                'cardNumber' => '4242 4242 4242 4242',
                'expiryMonth' => '01',
                'expiryYear' => '2025',
                'cvc' => '123',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
        ]);

        Event::assertDispatched(OrderPaid::class);
        Event::assertDispatched(OrderSuccessful::class);
    }
}
