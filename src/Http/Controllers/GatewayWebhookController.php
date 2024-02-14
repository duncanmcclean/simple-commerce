<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Events\GatewayWebhookReceived;
use DuncanMcClean\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DuncanMcClean\SimpleCommerce\Facades\Gateway;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;

class GatewayWebhookController extends BaseActionController
{
    public function index(Request $request, $gateway)
    {
        $gatewayName = $gateway;

        $gateway = SimpleCommerce::gateways()
            ->where('handle', $gateway)
            ->first();

        if (! $gateway) {
            throw new GatewayDoesNotExist("Gateway [{$gatewayName}] does not exist.");
        }

        event(new GatewayWebhookReceived($request->all()));

        return Gateway::use($gateway['handle'])->webhook($request);
    }
}
