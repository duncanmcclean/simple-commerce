<?php

namespace Damcclean\Commerce\Tags;

use Statamic\Tags\Tags;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CommerceTags extends Tags
{
    protected static $handle = 'commerce';

    public function currency()
    {
        return config('commerce.currency');
    }

    public function stripeKey()
    {
        return config('commerce.stripe.key');
    }

    public function stripeSecret()
    {
        return config('commerce.stripe.secret');
    }

    public function paymentIntent()
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));

        return PaymentIntent::create([
            'amount' => $this->getParam('amount'),
            'currency' => config('commerce.currency'),
        ]);
    }
}
