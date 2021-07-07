<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotSupportPurchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use Illuminate\Http\Request;

class PayPalGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'PayPal';
    }

    public function prepare(Prepare $data): Response
    {
        return new Response(true, [], 'https://....');
    }

    public function purchase(Purchase $data): Response
    {
        // We don't actually do anything here as PayPal is an
        // off-site gateway, so it has it's own checkout page.

        throw new GatewayDoesNotSupportPurchase("Gateway [paypal] does not support the `purchase` method.");
    }

    public function purchaseRules(): array
    {
        // PayPal is off-site, therefore doesn't use the traditional
        // checkout process provided by Simple Commerce. Hence why no rules
        // are defined here.

        return [];
    }

    public function getCharge(Order $order): Response
    {
        return new Response(true, []);
    }

    public function refundCharge(Order $order): Response
    {
        return new Response(true, []);
    }

    public function webhook(Request $request)
    {
        //
    }
}
