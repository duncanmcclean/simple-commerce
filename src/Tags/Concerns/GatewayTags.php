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
            ->where('handle', $this->getParam('handle'))
            ->first();
    }
}