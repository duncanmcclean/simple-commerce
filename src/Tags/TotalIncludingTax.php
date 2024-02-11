<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Currency;
use Statamic\Facades\Site;
use Statamic\Tags\Tags;

class TotalIncludingTax extends Tags
{
    public function index()
    {
        $total = $this->context->get('total')->raw();
        $taxAmount = $this->context->get('tax')->raw()['amount'];

        $totalIncludingTax = $total + $taxAmount;

        return Currency::parse($totalIncludingTax, Site::current());
    }
}
