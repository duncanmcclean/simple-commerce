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

        throw_if(! $gateway, new GatewayDoesNotExist(__('simple-commerce::gateways.gateway_does_not_exist', ['gateway' => $gateway])));

        $this->forgetSessionCart();

        return $this->withSuccess($request, [
            'success' => 'Successful checkout.',
        ]);
    }
}
