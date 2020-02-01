<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Events\CheckoutComplete;
use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\StripeGateway;
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
    public function can_get_checkout_without_items_in_cart()
    {
        $response = $this->get('/checkout');

        $response
            ->assertOk()
            ->assertSee('There are no items in your cart');
    }

    /** @test */
    public function can_get_checkout_view_with_items_in_cart()
    {
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
        ]);

        $cart = factory(Cart::class)->create();
        $this->session(['commerce_cart_id' => $cart->uid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->get('/checkout');

        $response
            ->assertOk()
            ->assertDontSee('There are no items in your cart')
            ->assertSee($variant->name)
            ->assertSee('Card Details')
            ->assertSee('window.paymentIntent');
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
        $this->session(['commerce_cart_id' => $cart->uid]);

        $cartItem = factory(CartItem::class)->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $data = [
            'payment_method' => (new StripeGateway())->randomPaymentMethod(),
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'shipping_address_1' => $this->faker->streetAddress,
            'shipping_address_2' => '',
            'shipping_address_3' => '',
            'shipping_city' => $this->faker->city,
            'shipping_country' => factory(Country::class)->create()->iso,
            'shipping_state' => '',
            'shipping_zip_code' => $this->faker->postcode,
            'use_shipping_address_for_billing' => 'on',
        ];

        $response = $this->post('/checkout/store', $data);

        $response->assertRedirect('/thank-you');
        $response->assertSessionHas('commerce_cart_id');

        $this->assertDatabaseHas('customers', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $customer = Customer::where('name', $data['name'])->first();

        $this->assertDatabaseHas('addresses', [
            'name' => $data['name'],
            'address1' => $data['shipping_address_1'],
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'payment_intent' => $data['payment_method'],
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseMissing('carts', [
            'uid' => $cart->uid,
        ]);

        Event::assertDispatched(CheckoutComplete::class);
    }
}
