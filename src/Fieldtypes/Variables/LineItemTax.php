<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes\Variables;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Facades\Site;

class LineItemTax extends VariableFieldtype
{
    protected static $handle = 'simple-commerce::line-item-tax';

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
