<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Exceptions\StripeNoPaymentIntentProvided;
use DoubleThreeDigital\SimpleCommerce\Exceptions\StripeSecretMissing;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer as SCCustomer;
use Exception;
use Statamic\Facades\Site;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;

class StripeGateway implements Gateway
{
    public function name(): string
    {
        return 'Stripe';
    }

    public function prepare(array $data): array
    {
        $cart = Cart::find(request()->session()->get(config('simple-commerce.cart_key')));
        $this->setUpWithStripe();

        $intentData = [
            'amount'   => $data['grand_total'],
            'currency' => Currency::get(Site::current())['code'],
            'description' => "Order: {$cart->title}",
            'metadata' => [
                'order_id' => $cart->id,
            ],
        ];

        if (isset($cart->data['customer'])) {
            $customer = SCCustomer::find($cart->data['customer']);

            $stripeCustomer = Customer::create([
                'name' => $customer->data['name'],
                'email' => $customer->data['email'],
            ]);

            $intentData['customer'] = $stripeCustomer->id;
        }

        $intent = PaymentIntent::create($intentData);

        return [
            'intent'         => $intent->id,
            'client_secret'  => $intent->client_secret,
        ];
    }

    public function purchase(array $data, $request): array
    {
        $this->setUpWithStripe();

        $paymentMethod = PaymentMethod::retrieve($data['payment_method']);

        return [
            'id'       => $paymentMethod->id,
            'object'   => $paymentMethod->object,
            'card'     => $paymentMethod->card->toArray(),
            'customer' => $paymentMethod->customer,
            'livemode' => $paymentMethod->livemode,
        ];
    }

    public function purchaseRules(): array
    {
        return [
            'payment_method' => 'required|string',
        ];
    }

    public function getCharge(array $data): array
    {
        return [];
    }

    public function refundCharge(array $data): array
    {
        $this->setUpWithStripe();

        if (! isset($data['intent'])) {
            throw new StripeNoPaymentIntentProvided(__('simple-commerce::gateways.stripe.no_payment_intent_provided'));
        }

        $refund = Refund::create([
            'payment_intent' => $data['intent'],
        ]);

        return json_decode($refund->toJSON(), true);
    }

    protected function setUpWithStripe()
    {
        if (! env('STRIPE_SECRET')) {
            throw new StripeSecretMissing(__('simple-commerce::gateways.stripe.stripe_secret_missing'));
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        if ($version = env('STRIPE_API_VERSION')) {
            Stripe::setApiVersion($version);
        }
    }
}
