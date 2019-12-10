<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Coupon;
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

        $coupon = Coupon::save($request->all());

        return ['redirect' => cp_route('coupons.edit', ['coupon' => $coupon['id']])];
    }

    public function edit($product)
    {
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
        $validated = []; // wip

        return Coupon::update(Coupon::find($coupon)->toArray()['id'], $request->all());
    }

    public function destroy($coupon)
    {
        $coupon = Coupon::delete(Coupon::find($coupon)['slug']);

        return redirect(cp_route('coupons.index'));
    }
}
