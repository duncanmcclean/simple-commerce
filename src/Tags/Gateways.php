<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Tags\Tags;

class Gateways extends Tags
{
    public function index()
    {
        return SimpleCommerce::gateways()->toArray();
    }

    public function count()
    {
        return SimpleCommerce::gateways()->count();
    }

    // {{ gateways:stripe }}
    public function wildcard(string $tag)
    {
        return SimpleCommerce::gateways()
            ->where('handle', $tag)
            ->first();
    }
}
