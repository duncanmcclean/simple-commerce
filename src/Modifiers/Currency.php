<?php

namespace DuncanMcClean\SimpleCommerce\Modifiers;

use DuncanMcClean\SimpleCommerce\Currency as CurrencyFacade;
use Statamic\Facades\Site;
use Statamic\Modifiers\Modifier;

class Currency extends Modifier
{
    public function index($value, $params, $context)
    {
        return CurrencyFacade::parse($value, Site::current());
    }
}
