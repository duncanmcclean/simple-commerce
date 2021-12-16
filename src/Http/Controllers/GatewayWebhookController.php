<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\ReceiveGatewayWebhook;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;

class GatewayWebhookController extends BaseActionController
{
    public function index(Request $request, $gateway)
    {
        $gatewayName = $gateway;

        $gateway = collect(SimpleCommerce::gateways())
            ->where('handle', $gateway)
            ->first();

        if (!$gateway) {
            throw new GatewayDoesNotExist("Gateway [{$gatewayName}] does not exist.");
        }

        event(new ReceiveGatewayWebhook($request->all()));

        return Gateway::use($gateway['class'])->webhook($request);
    }
}
