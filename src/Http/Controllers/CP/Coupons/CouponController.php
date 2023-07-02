<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\Coupons;

use DoubleThreeDigital\SimpleCommerce\Coupons\CouponBlueprint;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\CreateRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\DeleteRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\EditRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\IndexRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon\UpdateRequest;
use Statamic\Facades\Scope;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class CouponController
{
    public function index(IndexRequest $request)
    {
        $columns = CouponBlueprint::getBlueprint()
            ->fields()
            ->items()
            ->pluck('handle')
            ->map(function ($columnKey) {
                $field = CouponBlueprint::getBlueprint()->field($columnKey);

                return [
                    'handle' => $columnKey,
                    'title' => $field->display() ?? $field,
                ];
            })
            ->toArray();

        return view('simple-commerce::cp.coupons.index', [
            'couponsCount' => Coupon::all()->count(),
            'columns' => CouponBlueprint::getBlueprint()
                ->columns()
                ->filter(fn ($column) => in_array($column->field, collect($columns)->pluck('handle')->toArray()))
                ->rejectUnlisted()
                ->values(),
            'filters' => Scope::filters('simple-commerce.coupons'),
            'listingConfig' => [
                'preferencesPrefix' => 'simple-commerce.coupons',
                'requestUrl' => cp_route('simple-commerce.coupons.listing-api'),
                'listingUrl' => cp_route('simple-commerce.coupons.index'),
            ],
            'actionUrl' => '#',
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
        $fields = CouponBlueprint::getBlueprint()
            ->fields()
            ->addValues($request->validated())
            ->process()
            ->values();

        $coupon = Coupon::make()
            ->code(Str::upper($fields->get('code')))
            ->type($fields->get('type'))
            ->value($fields->get('value'))
            ->enabled($fields->get('enabled'))
            ->data(Arr::except($fields, ['code', 'type', 'value', 'enabled']));

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

        $fields = CouponBlueprint::getBlueprint()
            ->fields()
            ->addValues($request->validated())
            ->process()
            ->values();

        $coupon
            ->code(Str::upper($fields->get('code')))
            ->type($fields->get('type'))
            ->value($fields->get('value'))
            ->enabled($fields->get('enabled'))
            ->data(Arr::except($fields, ['code', 'type', 'value', 'enabled']))
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
