<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Contracts\Payments\Gateway;
use DuncanMcClean\SimpleCommerce\Facades\Cart as CartFacade;
use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use Statamic\Facades\Blink;
use Statamic\Tags\Tags;

class PaymentGateways extends Tags
{
    const BLINK_KEY = 'payment-gateways-loop';

    public function index()
    {
        $cart = CartFacade::current();

        if (! Blink::has(self::BLINK_KEY)) {
            Blink::put(self::BLINK_KEY, $this->getPaymentGateways($cart));
        }

        return Blink::get(self::BLINK_KEY);
    }

    private function getPaymentGateways($cart)
    {
        return PaymentGateway::all()
            ->map(function (Gateway $paymentGateway) use ($cart) {
                $setup = $paymentGateway->setup($cart);

                return [
                    'name' => $paymentGateway->name(),
                    'handle' => $paymentGateway->handle(),
                    'gateway_config' => $paymentGateway->config()->all(),
                    'callback_url' => route('statamic.simple-commerce.payments.callback', $paymentGateway->handle()),
                    ...$setup,
                ];
            })
            ->values()
            ->all();
    }
}
