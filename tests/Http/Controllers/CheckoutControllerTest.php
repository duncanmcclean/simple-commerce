<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\CartCompleted;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\PreCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Mail\BackOffice\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Mail\OrderConfirmation;
use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class CheckoutControllerTest extends TestCase
{
    use CollectionSetup;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function can_store_checkout()
    {
        // TODO: fix this test, it's doing funny things
        $this->markTestIncomplete();

        Event::fake();
        Mail::fake();

        $product = Product::create([
            'title' => 'Food',
            'price' => 1000,
        ]);

        Entry::make()
            ->collection('coupons')
            ->id(Stache::generateId())
            ->data([
                'title' => 'Half Price',
                'redeemed' => 0,
                'value' => 50,
                'type' => 'percentage',
            ])
            ->save();

        $coupon = Entry::whereCollection('coupons')->first();

        $cart = Order::create([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
            'coupon' => $coupon->id(),
        ]);

        $data = [
            'name' => 'Jimmy Bloggs',
            'email' => 'jimmy.bloggs@doublethree.digital',
            'gateway' => DummyGateway::class,

            'card_number' => '4242424242424242',
            'expiry_month' => 01,
            'expiry_year' => 2025,
            'cvc' => 123,

            'delivery_note' => 'Please be careful when delivering.',
            '_redirect' => '/checkout/thanks',
        ];

        $response = $this
            ->from('/checkout')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.checkout.store'), $data);

        $cart->find($cart->id);

        // Assert event is dispatched
        Event::assertDispatched(PreCheckout::class);

        // Assert customer has been created & set
        $customer = Customer::findByEmail($data['email']);
        $this->assertSame($cart->data['customer'], $customer->id);

        // Assert gateway has run and data has been saved
        $this->assertStringContainsString('DummyGateway', $cart->data['gateway']);
        $this->assertIsArray($cart->data['gateway_data']);

        // Assert coupon is redeemed
        $coupon->fresh();
        $this->assertSame(1, $coupon->data()->get('redeemed'));

        // Assert remaining data is saved
        $this->assertArrayHasKey('delivery_note', $cart->data);

        // Assert cart has been completed
        $this->assertTrue($cart->data['is_paid']);
        $this->assertSame($cart->data['order_status'], 'completed');
        Event::assertDispatched(CartCompleted::class);

        // Assert emails have been sent to customer and back office
        Mail::assertSent(OrderConfirmation::class);
        Mail::assertSent(OrderPaid::class);

        // Assert cart key is no longer in the session
        $this->assertFalse(session()->has('simple-commerce-cart'));

        // Assert another event is dispatched
        Event::assertDispatched(PostCheckout::class);

        // Assert a redirect happens correctly
        $response->assertRedirect('/checkout/thanks');
    }
}
