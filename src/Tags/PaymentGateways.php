<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Facades\Cart as CartFacade;
use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\PaymentGateway;
use Statamic\Facades\Blink;
use Statamic\Tags\Tags;

class PaymentGateways extends Tags
{
    const BLINK_KEY = 'payment-gateways-loop';

    public function index()
    {
        $cart = CartFacade::current();

        if ($cart->isFree()) {
            return [];
        }

        if (! Blink::has(self::BLINK_KEY)) {
            Blink::put(self::BLINK_KEY, $this->getPaymentGateways($cart));
        }

        return Blink::get(self::BLINK_KEY);
    }

    private function getPaymentGateways($cart)
    {
        return Facades\PaymentGateway::all()
            ->map(function (PaymentGateway $paymentGateway) use ($cart) {
                $setup = $paymentGateway->setup($cart);

                return [
                    'name' => $paymentGateway->title(),
                    'handle' => $paymentGateway->handle(),
                    'gateway_config' => $paymentGateway->config()->all(),
                    'checkout_url' => $paymentGateway->checkoutUrl(),
                    ...$setup,
                ];
            })
            ->values()
            ->all();
    }
}
