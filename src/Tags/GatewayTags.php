<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class GatewayTags extends SubTag
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