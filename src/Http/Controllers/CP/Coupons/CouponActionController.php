<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Coupons;

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Http\Resources\CP\Coupons\Coupon as CouponResource;
use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;

class CouponActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Coupon::find($item);
        });
    }

    protected function getItemData($coupon, $context): array
    {
        $coupon = $coupon->fresh();
        $blueprint = Coupon::blueprint();

        return array_merge((new CouponResource($coupon))->resolve()['data'], [
            'itemActions' => Action::for($coupon, $context),
        ]);
    }
}
