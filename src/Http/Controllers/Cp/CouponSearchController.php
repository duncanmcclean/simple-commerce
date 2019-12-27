<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Coupon;
use Statamic\Http\Controllers\CP\CpController;

class CouponSearchController extends CpController
{
    public function __invoke()
    {
        $query = request()->input('search');

        if (! $query) {
            $results = Coupon::all()
                ->map(function ($coupon) {
                    return array_merge($coupon->toArray(), [
                        'edit_url' => cp_route('coupons.edit', ['coupon' => $coupon['id']]),
                        'delete_url' => cp_route('coupons.destroy', ['coupon' => $coupon['id']]),
                    ]);
                });
        } else {
            $results = Coupon::all()
                ->filter(function ($item) use ($query) {
                    return false !== stristr((string) $item['slug'], $query);
                })
                ->map(function ($coupon) {
                    return array_merge($coupon->toArray(), [
                        'edit_url' => cp_route('coupons.edit', ['coupon' => $coupon['id']]),
                        'delete_url' => cp_route('coupons.destroy', ['coupon' => $coupon['id']]),
                    ]);
                });
        }

        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('coupons.search'),
                'sortColumn' => 'title',
            ],
        ]);
    }
}
