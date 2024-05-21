<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Money;
use Statamic\Facades\Site;
use Statamic\Tags\Tags;

class TotalIncludingTax extends Tags
{
    public function index()
    {
        $total = $this->context->get('total')->raw();
        $taxAmount = $this->context->get('tax')->raw()['amount'];

        $totalIncludingTax = $total + $taxAmount;

        return Money::format($totalIncludingTax, Site::current());
    }
}
