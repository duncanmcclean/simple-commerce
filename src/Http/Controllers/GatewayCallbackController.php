<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;

class GatewayCallbackController extends BaseActionController
{
    use CartDriver;

    public function index(Request $request, $gateway)
    {
        $gateway = collect(SimpleCommerce::gateways())
            ->where('handle', $gateway)
            ->first();

        if (! $gateway) {
            throw new GatewayDoesNotExist(__('simple-commerce::gateways.gateway_does_not_exist', [
                'gateway' => $gateway['name'],
            ]));
        }

        $this->forgetCart();

        return $this->withSuccess($request, [
            'success' => __('simple-commerce.messages.checkout_complete'),
        ]);
    }
}
