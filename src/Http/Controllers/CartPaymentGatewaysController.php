<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Facades\Cart as CartFacade;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\PaymentGateway;

class CartPaymentGatewaysController
{
    public function __invoke()
    {
        $cart = CartFacade::current();

        return Facades\PaymentGateway::all()
            ->map(function (PaymentGateway $paymentGateway) use ($cart) {
                $setup = $cart->isFree() ? [] : $paymentGateway->setup($cart);

                return [
                    'name' => $paymentGateway->title(),
                    'handle' => $paymentGateway->handle(),
                    'checkout_url' => $paymentGateway->checkoutUrl(),
                    ...$setup,
                ];
            })
            ->values()
            ->all();
    }
}
