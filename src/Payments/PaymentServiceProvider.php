<?php

namespace DuncanMcClean\SimpleCommerce\Payments;

use DuncanMcClean\SimpleCommerce\Events\CartRecalculated;
use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use Illuminate\Support\Facades\Event;
use Statamic\Providers\AddonServiceProvider;

class PaymentServiceProvider extends AddonServiceProvider
{
    protected array $paymentGateways = [
        Gateways\Dummy::class,
//        Gateways\Mollie::class,
        Gateways\Stripe::class,
    ];

    public function bootAddon()
    {
        foreach ($this->paymentGateways as $paymentGateway) {
            $paymentGateway::register();
        }

        Event::listen(CartRecalculated::class, function ($event) {
            PaymentGateway::all()->each(fn ($gateway) => $gateway->afterRecalculating($event->cart));
        });
    }
}