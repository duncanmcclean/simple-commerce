<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes\Variables;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Facades\Site;

class LineItemTax extends VariableFieldtype
{
    protected $component = 'hidden';
    protected static $handle = 'sc_line_items_tax';

    public static function title()
    {
        return 'SC: Line Item Tax';
    }

    public function augment($value)
    {
        $value['amount'] = Currency::parse($value['amount'], Site::current());

        return $value;
    }
}
