<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\CouponRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class CouponController extends CpController
{
    public function index()
    {
        $this->authorize('view', Coupon::class);

        return view('simple-commerce::cp.coupons.index', [
            'coupons' => Coupon::paginate(config('statamic.cp.pagination_size')),
            'createUrl' => (new Coupon())->createUrl(), 
        ]);
    }

    public function create()
    {
        $this->authorize('create', Coupon::class);
        
        $crumbs = Breadcrumbs::make(['text' => 'Simple Commerce'], ['text' => 'Coupons', 'url' => cp_route('coupons.index')]);

        $blueprint = (new Coupon())->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('simple-commerce::cp.coupons.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
            'action'    => cp_route('coupons.store'),
        ]);
    }

    public function store(CouponRequest $request)
    {
        $this->authorize('create', Coupon::class);

        $coupon = Coupon::create([
            'uuid' => (new Stache())->generateId(),
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'minimum_total' => $request->minimum_total,
            'total_uses' => $request->total_uses,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return [
            'redirect' => cp_route('coupons.edit', [
                'coupon' => $coupon->uuid,
            ]),
        ];
    }

    public function edit($coupon)
    {
        $this->authorize('update', $coupon);

        $crumbs = Breadcrumbs::make(['text' => 'Simple Commerce'], ['text' => 'Coupons', 'url' => cp_route('coupons.index')]);

        $coupon = Coupon::where('uuid', $coupon)->first();

        $values = $coupon->toArray();
        $blueprint = (new Coupon())->blueprint();
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        return view('simple-commerce::cp.coupons.edit', [
            'crumbs'    => $crumbs,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'action'    => $coupon->updateUrl(),
        ]);
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $this->authorize('update', $coupon);

        $coupon->update([
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'minimum_total' => $request->minimum_total,
            'total_uses' => $request->total_uses,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $coupon;
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorize('delete', $coupon);

        $coupon->delete();
    }
}