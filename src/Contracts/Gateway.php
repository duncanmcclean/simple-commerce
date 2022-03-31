<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use Illuminate\Http\Request;

interface Gateway
{
    public function name(): string;

    public function prepare(Prepare $data): Response;

    public function purchase(Purchase $data): Response;

    public function purchaseRules(): array;

    public function purchaseMessages(): array;

    public function getCharge(Order $order): Response;

    public function refundCharge(Order $order): Response;

    public function callback(Request $request): bool;

    public function webhook(Request $request);

    public function isOffsiteGateway(): bool;
}
