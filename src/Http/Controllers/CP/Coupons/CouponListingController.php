<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Coupons;

use DuncanMcClean\SimpleCommerce\Coupons\CouponBlueprint;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Http\Resources\CP\Coupons\Coupons;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class CouponListingController extends CpController
{
    use QueriesFilters;

    public function index(FilteredRequest $request)
    {
        $blueprint = CouponBlueprint::getBlueprint();

        if (! User::current()->can('view coupons')) {
            abort(403);
        }

        $coupons = Coupon::query();

        if ($searchQuery = $request->input('search')) {
            $coupons->where('code', 'like', '%'.$searchQuery.'%');
        }

        $activeFilterBadges = $this->queryFilters($coupons, $request->filters, [
            'blueprints' => [$blueprint],
        ]);

        $coupons = $coupons->paginate(
            $request->input('perPage',
                config('statamic.cp.pagination_size'))
        );

        return (new Coupons($coupons))
            ->blueprint(CouponBlueprint::getBlueprint())
            ->columnPreferenceKey('simple_commerce.coupons.columns')
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
    }
}
