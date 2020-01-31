<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;

class StripeGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));
    }

    public function issueRefund(string $paymentIntent)
    {
        return Refund::create(['payment_intent' => $paymentIntent]);
    }

    public function setupIntent(string $amount, string $currencyIso)
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currencyIso,
        ]);
    }

    public function completeIntent(string $paymentMethod)
    {
        return PaymentMethod::retrieve($paymentMethod);
    }
}
