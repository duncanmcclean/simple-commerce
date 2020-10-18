<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface GatewayRepository
{
    public function use($className): self;

    public function name();

    public function prepare($request, $order);

    public function purchase($request, $order);

    public function purchaseRules();

    public function getCharge($order);

    public function refundCharge($order);
}
