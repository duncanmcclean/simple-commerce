<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

trait GatewayTags
{
    public function gateways()
    {
        return SimpleCommerce::gateways();
    }

    public function gateway()
    {
        return collect(SimpleCommerce::gateways())
            ->where('name', $this->getParam('name'))
            ->first();
    }
}