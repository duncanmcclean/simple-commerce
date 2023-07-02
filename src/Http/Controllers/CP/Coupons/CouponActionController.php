<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\Coupons;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\ActionController;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;

class CouponActionController extends ActionController
{
    public function runAction(Request $request)
    {
        return parent::run($request);
    }

    public function bulkActionsList(Request $request)
    {
        return parent::bulkActions($request);
    }

    protected function getSelectedItems($items, $context)
    {
        return $items->map(fn ($item) => Coupon::find($item));
    }
}
