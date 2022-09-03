<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\CreateRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\DeleteRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\EditRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\IndexRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\UpdateRequest;
use Statamic\Facades\Stache;

class CouponController
{
    public function index(IndexRequest $request)
    {
        return view('simple-commerce::cp.coupons.index', [
            'coupons' => Coupon::all(),
        ]);
    }

    public function create(CreateRequest $request)
    {
        return view('simple-commerce::cp.coupons.create');
    }

    public function store(StoreRequest $request)
    {
        // $taxZone = TaxZone::make()
        //     ->id(Stache::generateId())
        //     ->name($request->name)
        //     ->country($request->country);

        // if ($request->region) {
        //     $taxZone->region($request->region);
        // }

        // $taxZone->save();

        // return redirect(cp_route('simple-commerce.tax-zones.index'));
    }

    public function edit(EditRequest $request, $taxZone)
    {
        // $taxZone = TaxZone::find($taxZone);

        // return view('simple-commerce::cp.tax-zones.edit', [
        //     'taxZone' => $taxZone,
        //     'countries' => Countries::sortBy('name')->all(),
        // ]);
    }

    public function update(UpdateRequest $request, $taxZone)
    {
        // $taxZone = TaxZone::find($taxZone)
        //     ->name($request->name)
        //     ->country($request->country);

        // if ($request->region) {
        //     $taxZone->region($request->region);
        // }

        // $taxZone->save();

        // return redirect($taxZone->editUrl());
    }

    public function destroy(DeleteRequest $request, $taxZone)
    {
        Coupon::find($taxZone)->delete();

        return redirect(cp_route('simple-commerce.coupons.index'));
    }
}
