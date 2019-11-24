<?php

namespace Damcclean\Commerce\Http\Controllers;

use Facades\Damcclean\Commerce\Models\Coupon;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class CouponController extends CpController
{
    public function index()
    {
        return view('commerce::cp.coupons.index', [
            'coupons' => Coupon::all()
        ]);
    }

    public function create()
    {
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
        $validated = []; // WIP

        $slug = str_slug($request->title);

        $coupon = Coupon::save($slug, $request->all());

        return array_merge($coupon->toArray(), [
            'redirect' => cp_route('coupons.edit', ['coupon' => $slug])
        ]);
    }

    public function edit($product)
    {
        $coupon = Coupon::get($product);

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
        $validated = []; // wip

        $coupon = Coupon::update($coupon, $request->all());

        if ($request->slug != $coupon) {
            return array_merge($coupon->toArray(), [
                'redirect' => cp_route('coupons.edit', ['coupon' => $coupon->slug])
            ]);
        }
    }

    public function destroy($coupon)
    {
        $coupon = Coupon::delete($coupon);

        return redirect(cp_route('coupons.index'));
    }
}
