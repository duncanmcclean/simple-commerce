<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPurchase;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayResponse;
use Illuminate\Http\Request;
use Statamic\Entries\Entry;

interface Gateway
{
    public function name(): string;

    public function prepare(GatewayPrep $data): GatewayResponse;

    public function purchase(GatewayPurchase $data): GatewayResponse;

    public function purchaseRules(): array;

    public function getCharge(Entry $order): GatewayResponse;

    public function refundCharge(Entry $order): GatewayResponse;

    public function webhook(Request $request);
}
