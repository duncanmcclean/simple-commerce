<?php

namespace Damcclean\Commerce\Tags;

use Statamic\Tags\Tags;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CommerceTags extends Tags
{
    protected static $handle = 'commerce';

    public function currencyCode()
    {
        return config('commerce.currency.code');
    }

    public function currencySymbol()
    {
        return config('commerce.currency.symbol');
    }

    public function stripeKey()
    {
        return config('commerce.stripe.key');
    }
}
