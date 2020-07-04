<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Facades\Site;
use Stripe\Stripe;
use Stripe\Exception\AuthenticationException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

class StripeGateway implements Gateway
{
    public function name(): string
    {
        return 'Stripe';
    }

    public function prepare(array $data): array
    {
        $this->setUpWithStripe();

        $intent = PaymentIntent::create([
            'amount' => $data['grand_total'],
            'currency' => Currency::get(Site::current())['code'],
        ]);

        return [
            'intent' => $intent->id,
            'client_secret' => $intent->client_secret,
        ];
    }

    public function purchase(array $data): array
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
        return [];
    }

    protected function setUpWithStripe()
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
        } catch (AuthenticationException $e) {
            throw new \Exception('Authentication to Stripe failed. Check your API keys are valid.');
        }
    }
}