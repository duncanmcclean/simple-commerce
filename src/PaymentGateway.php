<?php

namespace Damcclean\Commerce;

use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));
    }

    public function paymentIntent($amount)
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => config('commerce.currency'),
        ]);
    }

    public function createCustomer()
    {
        //
    }

    public function createOrder()
    {
        //
    }
}
