<?php

namespace DuncanMcClean\SimpleCommerce\Payments;

use Statamic\Providers\AddonServiceProvider;

class PaymentServiceProvider extends AddonServiceProvider
{
    protected array $paymentGateways = [
        Gateways\Dummy::class,
        Gateways\Mollie::class,
        Gateways\Stripe::class,
    ];

    public function bootAddon()
    {
        foreach ($this->paymentGateways as $paymentGateway) {
            $paymentGateway::register();
        }
    }
}
