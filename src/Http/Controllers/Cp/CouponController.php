<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Coupon;
use Damcclean\Commerce\Policies\CouponPolicy;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class CouponController extends CpController
{
    public function index()
    {
        $this->authorize('view', CouponPolicy::class);

        return view('commerce::cp.coupons.index', [
            'coupons' => Coupon::all()
        ]);
    }

    public function create()
    {
        $this->authorize('create', CouponPolicy::class);

        $blueprint = Blueprint::find('coupon');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.coupons.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', CouponPolicy::class);

        $validated = []; // WIP

        $coupon = Coupon::save($request->all());

        return array_merge($coupon, [
            'redirect' => cp_route('coupons.edit', ['coupon' => $coupon['id']])
        ]);
    }

    public function edit($product)
    {
        $this->authorize('edit', CouponPolicy::class);

        $coupon = Coupon::find($product);

        $blueprint = Blueprint::find('coupon');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.coupons.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $coupon,
            'meta'      => $fields->meta(),
        ]);
    }

    public function update(Request $request, $coupon)
    {
        $this->authorize('edit', CouponPolicy::class);

        $validated = []; // wip

        $coupon = Coupon::update(Coupon::find($coupon)['slug'], $request->all());

        if ($request->slug != $coupon) {
            return array_merge($coupon->toArray(), [
                'redirect' => cp_route('coupons.edit', ['coupon' => $coupon['id']])
            ]);
        }
    }

    public function destroy($coupon)
    {
        $this->authorize('delete', CouponPolicy::class);

        $coupon = Coupon::delete(Coupon::find($coupon)['slug']);

        return redirect(cp_route('coupons.index'));
    }
}
