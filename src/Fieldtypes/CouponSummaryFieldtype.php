<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Money;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;

class CouponSummaryFieldtype extends Fieldtype
{
    protected $selectable = false;

    protected $component = 'coupon-summary';

    public function preload()
    {
        return [
            'currency' => Money::get(Site::current()),
        ];
    }
}
