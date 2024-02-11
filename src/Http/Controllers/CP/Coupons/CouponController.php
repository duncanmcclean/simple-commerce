<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Coupons;

use DuncanMcClean\SimpleCommerce\Coupons\CouponBlueprint;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon\CreateRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon\EditRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon\IndexRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon\StoreRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon\UpdateRequest;
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
                'preferencesPrefix' => 'simple_commerce.coupons',
                'requestUrl' => cp_route('simple-commerce.coupons.listing-api'),
                'listingUrl' => cp_route('simple-commerce.coupons.index'),
            ],
            'actionUrl' => cp_route('simple-commerce.coupons.actions.run'),
        ]);
    }

    public function create(CreateRequest $request)
    {
        $blueprint = CouponBlueprint::getBlueprint();
        $blueprint = $blueprint->removeField('redeemed');

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
            ->data(Arr::except($fields, ['code', 'type', 'value']));

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
        $fields = $fields->addValues($coupon->toArray())->setParent($coupon);
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
            ->data(Arr::except($fields, ['code', 'type', 'value']))
            ->save();

        return [
            'coupon' => $coupon,
        ];
    }
}
