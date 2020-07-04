<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Sites\Site;
use Stripe\Stripe;
use Stripe\Exception\AuthenticationException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

class StripeGateway implements Gateway
{
    public static $name = 'Stripe';
    public static $description = 'First-party Stripe gateway for Simple Commerce';

    public function prepare(array $data)
    {
        $this->setUpWithStripe();

        $intent = PaymentIntent::create([
            'amount' => $data['amount'],
            'currency' => Currency::get(Site::current())['code'],
        ]);

        return $intent;
    }

    public function purchase(array $data): array
    {
        return PaymentMethod::retrieve($data['payment_method']);
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