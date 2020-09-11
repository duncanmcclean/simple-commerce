<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use Statamic\Entries\Entry;

class MollieGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Mollie';
    }

    public function prepare(GatewayPrep $data): GatewayResponse
    {

    }

    public function purchase(GatewayPurchase $data): GatewayResponse
    {

    }

    public function purchaseRules(): array
    {
        return [

        ];
    }

    public function getCharge(Entry $order): GatewayResponse
    {

    }

    public function refundCharge(Entry $order): GatewayResponse
    {

    }
}
