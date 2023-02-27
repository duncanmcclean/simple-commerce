<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class GatewayTags extends SubTag
{
    public function index()
    {
        return SimpleCommerce::gateways()->toArray();
    }

    public function count()
    {
        return SimpleCommerce::gateways()->count();
    }

    // {{ sc:gateways:stripe }}
    public function wildcard(string $tag)
    {
        return SimpleCommerce::gateways()
            ->where('handle', $tag)
            ->first();
    }
}
