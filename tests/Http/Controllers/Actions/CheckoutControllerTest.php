<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\StripeGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        factory(Currency::class)->create();
        factory(OrderStatus::class)->create(['primary' => true]);
    }

    /** @test */
    public function can_store_checkout()
    {
        Event::fake();

        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uuid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id'       => $cart->id,
            'product_id'    => $product->id,
            'variant_id'    => $variant->id,
            'quantity'      => 1,
        ]);

        $data = [
            'gateway'                           => 'DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway',
            'cardholder'                        => 'Mr Joe Bloggs',
            'cardNumber'                        => '4242 4242 4242 4242',
            'expiryMonth'                       => '07',
            'expiryYear'                        => '2025',
            'cvc'                               => '123',

            'name'                              => $this->faker->name,
            'email'                             => $this->faker->email,
            'shipping_address_1'                => $this->faker->streetAddress,
            'shipping_address_2'                => '',
            'shipping_address_3'                => '',
            'shipping_city'                     => $this->faker->city,
            'shipping_country'                  => factory(Country::class)->create()->iso,
            'shipping_state'                    => '',
            'shipping_zip_code'                 => $this->faker->postcode,
            'use_shipping_address_for_billing'  => 'on',
            'redirect'                          => '/thank-you',
        ];

        $response = $this->post(route('statamic.simple-commerce.checkout.store'), $data);

        $response->assertRedirect('/thank-you');
        $response->assertSessionHas('commerce_cart_id');

        $this->assertDatabaseHas('customers', [
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        $customer = Customer::where('name', $data['name'])->first();

        $this->assertDatabaseHas('addresses', [
            'name'          => $data['name'],
            'address1'      => $data['shipping_address_1'],
            'customer_id'   => $customer->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'customer_id'   => $customer->id,
        ]);

        $this->assertDatabaseMissing('carts', [
            'uuid'  => $cart->uuid,
        ]);

        Event::assertDispatched(OrderPaid::class);
        Event::assertDispatched(OrderSuccessful::class);
    }
}
