<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Illuminate\Support\Str;
use Statamic\Fieldtypes\Text;

class CouponCodeFieldtype extends Text
{
    protected $selectable = false;

    protected $component = 'coupon-code';

    public function process($data)
    {
        return Str::of($data)->upper()->__toString();
    }
}
