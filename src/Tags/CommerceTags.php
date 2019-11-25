<?php

namespace Damcclean\Commerce\Tags;

use Damcclean\Commerce\PaymentGateway;
use Statamic\Tags\Tags;

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
        return (new PaymentGateway())->paymentIntent($this->getParam('amount'))['client_secret'];
    }
}
