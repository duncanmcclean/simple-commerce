<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\SessionCart;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;

class GatewayCallbackController extends BaseActionController
{
    use SessionCart;

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

        $this->forgetSessionCart();

        return $this->withSuccess($request, [
            'success' => 'Successful checkout.',
        ]);
    }
}
