<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CouponStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CouponUpdateRequest;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class CouponController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
        ]);

        $coupons = Coupon::all()
            ->map(function ($coupon) {
                return array_merge($coupon->toArray(), [
                    'edit_url' => cp_route('coupons.edit', ['coupon' => $coupon['id']]),
                    'delete_url' => cp_route('coupons.destroy', ['coupon' => $coupon['id']]),
                ]);
            });

        return view('commerce::cp.coupons.index', [
            'coupons' => $coupons,
            'crumbs' => $crumbs,
        ]);
    }

    public function create()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Coupons', 'url' => cp_route('coupons.index')],
        ]);

        $blueprint = Blueprint::find('coupon');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.coupons.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function store(CouponStoreRequest $request)
    {
        $validated = $request->validated();

        $coupon = Coupon::save($request->all());

        return ['redirect' => cp_route('coupons.edit', ['coupon' => $coupon->data['id']])];
    }

    public function edit($product)
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Coupons', 'url' => cp_route('coupons.index')],
        ]);

        $coupon = Coupon::find($product);

        $blueprint = Blueprint::find('coupon');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.coupons.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $coupon,
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function update(CouponUpdateRequest $request, $coupon)
    {
        $validated = $request->validated();

        return Coupon::update($coupon, $request->all());
    }

    public function destroy($coupon)
    {
        $coupon = Coupon::delete(Coupon::find($coupon)['slug']);

        return redirect(cp_route('coupons.index'));
    }
}
