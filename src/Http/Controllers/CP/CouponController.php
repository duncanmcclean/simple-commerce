<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Coupons\CouponBlueprint;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\CreateRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\DeleteRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\EditRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\IndexRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\UpdateRequest;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Statamic\Support\Str;

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
        $blueprint = CouponBlueprint::getBlueprint();

        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('simple-commerce::cp.coupons.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $coupon = Coupon::make()
            ->code(Str::upper($request->code))
            ->type($request->type)
            ->value($request->value)
            ->data(Arr::except($request->validated(), ['code', 'type', 'value']));

        $coupon->save();

        return [
            'redirect' => $coupon->editUrl(),
        ];
    }

    public function edit(EditRequest $request, $coupon)
    {
        $coupon = Coupon::find($coupon);

        if (! $coupon) {
            abort(404);
        }

        $blueprint = CouponBlueprint::getBlueprint();

        $fields = $blueprint->fields();
        $fields = $fields->addValues($coupon->toArray());
        $fields = $fields->preProcess();

        return view('simple-commerce::cp.coupons.edit', [
            'coupon' => $coupon,

            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ]);
    }

    public function update(UpdateRequest $request, $coupon)
    {
        $coupon = Coupon::find($coupon);

        $coupon
            ->code(Str::upper($request->code))
            ->type($request->type)
            ->value($request->value)
            ->data(Arr::except($request->validated(), ['code', 'type', 'value']))
            ->save();

        return [
            'coupon' => $coupon,
        ];
    }

    public function destroy(DeleteRequest $request, $coupon)
    {
        Coupon::find($coupon)->delete();

        return redirect(cp_route('simple-commerce.coupons.index'));
    }
}
