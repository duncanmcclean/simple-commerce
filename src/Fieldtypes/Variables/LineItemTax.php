<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes\Variables;

use DuncanMcClean\SimpleCommerce\Currency;
use Statamic\Facades\Site;

class LineItemTax extends VariableFieldtype
{
    protected static $handle = 'sc_line_items_tax';

    public static function title()
    {
        return __('Simple Commerce: Line Item Tax');
    }

    public function augment($value)
    {
        if (! isset($value['amount'])) {
            return $value;
        }

        $value['amount'] = Currency::parse($value['amount'], Site::current());

        return $value;
    }
}
