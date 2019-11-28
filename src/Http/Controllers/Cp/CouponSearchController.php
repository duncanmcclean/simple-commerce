<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Facades\Damcclean\Commerce\Models\Coupon;
use Statamic\Http\Controllers\CP\CpController;

class CouponSearchController extends CpController
{
    public function __invoke()
    {
        $results = Coupon::search(request()->input('search'));

        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('coupons.search'),
                'sortColumn' => 'title',
            ]
        ]);
    }
}
