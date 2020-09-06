<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Gateways\GatewayPurchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\GatewayResponse;
use Statamic\Entries\Entry;

interface Gateway
{
    public function name(): string;

    public function prepare(GatewayPrep $data): GatewayResponse;

    public function purchase(GatewayPurchase $data): GatewayResponse;

    public function purchaseRules(): array;

    public function getCharge(Entry $order): GatewayResponse;

    public function refundCharge(Entry $order): GatewayResponse;
}
