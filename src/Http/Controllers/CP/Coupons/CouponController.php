<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Coupons;

use DuncanMcClean\SimpleCommerce\Coupons\CouponBlueprint;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use Illuminate\Http\Request;
use Statamic\CP\PublishForm;
use Statamic\Facades\Scope;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class CouponController
{
    public function index(Request $request)
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

    public function create(Request $request)
    {
        $blueprint = CouponBlueprint::getBlueprint();
        $blueprint = $blueprint->removeField('redeemed');

        return PublishForm::make($blueprint)
            ->title('Create Coupon')
            ->submittingTo(cp_route('simple-commerce.coupons.store'), 'POST');
    }

    public function store(Request $request)
    {
        $values = PublishForm::make(CouponBlueprint::getBlueprint())->submit($request->values);

        $coupon = Coupon::make()
            ->code(Str::upper($values['code']))
            ->type($values['type'])
            ->value($values['value'])
            ->data(Arr::except($values, ['code', 'type', 'value']));

        $coupon->save();

        return [
            'redirect' => $coupon->editUrl(),
        ];
    }

    public function edit(Request $request, $coupon)
    {
        $coupon = Coupon::find($coupon);

        if (! $coupon) {
            abort(404);
        }

        return PublishForm::make(CouponBlueprint::getBlueprint())
            ->title('Edit Coupon')
            ->values($coupon->toArray())
            ->parent($coupon)
            ->submittingTo($coupon->updateUrl());
    }

    public function update(Request $request, $coupon)
    {
        $values = PublishForm::make(CouponBlueprint::getBlueprint())->submit($request->values);

        $coupon = Coupon::find($coupon);

        $coupon
            ->code(Str::upper($values['code']))
            ->type($values['type'])
            ->value($values['value'])
            ->data(Arr::except($values, ['code', 'type', 'value']))
            ->save();

        return [];
    }
}
