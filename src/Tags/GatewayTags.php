<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class GatewayTags extends SubTag
{
    public function index()
    {
        return SimpleCommerce::gateways();
    }

    public function count()
    {
        return count(SimpleCommerce::gateways());
    }

    // {{ sc:gateways:stripe }}
    public function wildcard(string $tag)
    {
        return collect(SimpleCommerce::gateways())
            ->where('handle', $this->params->get('handle'))
            ->first();
    }
}
