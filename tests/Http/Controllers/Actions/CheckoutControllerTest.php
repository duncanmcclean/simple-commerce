<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Actions;

use App\User;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
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

                'shipping_address_1'                => $this->faker->streetAddress,
                'shipping_address_2'                => '',
                'shipping_address_3'                => '',
                'shipping_city'                     => $this->faker->city,
                'shipping_country'                  => factory(Country::class)->create()->iso,
                'shipping_state'                    => '',
                'shipping_zip_code'                 => $this->faker->postcode,
                'use_shipping_address_for_billing'  => 'on',
            ])
            ->assertRedirect();

        $customer = User::where('email', 'george@murray.com')->first(); 

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('variants', [
            'id' => $lineItems[0]['id'],
            'stock' => ($lineItems[0]['variant']['stock'] - 1),
        ]);

        $this->assertDatabaseHas('variants', [
            'id' => $lineItems[1]['id'],
            'stock' => ($lineItems[1]['variant']['stock'] - 1),
        ]);

        Event::assertDispatched(OrderPaid::class);
        Event::assertDispatched(OrderSuccessful::class);
    }
}
