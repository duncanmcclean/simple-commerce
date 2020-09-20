<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;

class GatewayCallbackController extends BaseActionController
{
    public function index(Request $request, $gateway)
    {
        $gateway = collect(SimpleCommerce::gateways())
            ->where('handle', $gateway)
            ->first();

        throw_if(! $gateway, new GatewayDoesNotExist(__('simple-commerce::gateways.gateway_does_not_exist')));

        // TODO: deal with redirect param
        // TODO: clear order from session

        return redirect('/')
            ->with('success', 'Successful checkout.');
    }
}
