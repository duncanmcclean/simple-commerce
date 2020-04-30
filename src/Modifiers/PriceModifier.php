<?php

namespace DoubleThreeDigital\SimpleCommerce\Modifiers;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Modifiers\Modifier;

class PriceModifier extends Modifier
{
    protected static $handle = 'price';

    public function index($value, $params, $context)
    {
        return Currency::parse($value, true, true);
    }
}
