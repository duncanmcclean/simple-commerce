<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Coupons;

use DuncanMcClean\SimpleCommerce\Contracts\Coupons\Coupon as CouponContract;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Http\Resources\CP\Coupons\Coupon as CouponResource;
use DuncanMcClean\SimpleCommerce\Http\Resources\CP\Coupons\Coupons;
use DuncanMcClean\SimpleCommerce\Rules\UniqueCouponValue;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Action;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class CouponController extends CpController
{
    use ExtractsFromCouponFields, QueriesFilters;

    public function index(FilteredRequest $request)
    {
        $this->authorize('index', CouponContract::class, __('You are not authorized to view coupons.'));

        if ($request->wantsJson()) {
            $query = $this->indexQuery();

            $activeFilterBadges = $this->queryFilters($query, $request->filters);

            $sortField = request('sort');
            $sortDirection = request('order', 'asc');

            if (! $sortField && ! request('search')) {
                $sortField = 'code';
                $sortDirection = 'desc';
            }

            if ($sortField) {
                $query->orderBy($sortField, $sortDirection);
            }

            $coupons = $query->paginate(request('perPage'));

            return (new Coupons($coupons))
                ->blueprint(Coupon::blueprint())
                ->columnPreferenceKey('simple-commerce.coupons.columns')
                ->additional(['meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ]]);
        }

        $blueprint = Coupon::blueprint();

        $columns = $blueprint->columns()
            ->setPreferred('simple-commerce.coupons.columns')
            ->rejectUnlisted()
            ->values();

        if (Coupon::query()->count() === 0) {
            return view('simple-commerce::cp.coupons.empty');
        }

        return view('simple-commerce::cp.coupons.index', [
            'blueprint' => $blueprint,
            'columns' => $columns,
            'filters' => Scope::filters('coupons'),
        ]);
    }

    protected function indexQuery()
    {
        $query = Coupon::query();

        if ($search = request('search')) {
            $query
                ->where('code', 'LIKE', '%'.$search.'%')
                ->orWhere('description', 'LIKE', '%'.$search.'%');
        }

        return $query;
    }

    public function create(Request $request)
    {
        $this->authorize('create', CouponContract::class);

        $blueprint = Coupon::blueprint();

        $values = Coupon::make()->data()->all();

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values();

        $viewData = [
            'title' => __('Create Coupon'),
            'actions' => [
                'save' => cp_route('simple-commerce.coupons.store'),
            ],
            'values' => $values->all(),
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
            'breadcrumbs' => new Breadcrumbs([
                ['text' => __('Coupons'), 'url' => cp_route('simple-commerce.coupons.index')],
            ]),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('simple-commerce::cp.coupons.create', $viewData);
    }

    public function store(Request $request)
    {
        $this->authorize('store', CouponContract::class);

        $blueprint = Coupon::blueprint();

        $data = $request->all();

        $fields = $blueprint->fields()->addValues($data);

        $fields->validator()->withRules([
            'code' => [new UniqueCouponValue],
        ])->validate();

        $values = $fields->process()->values();

        $coupon = Coupon::make()
            ->code($values->get('code'))
            ->type($values->get('type'))
            ->amount($values->get('amount'))
            ->data($values->except(['code', 'type', 'amount']));

        $saved = $coupon->save();

        return [
            'data' => (new CouponResource($coupon))->resolve()['data'],
            'saved' => $saved,
        ];
    }

    public function edit(Request $request, $coupon)
    {
        $coupon = Coupon::find($coupon);

        $this->authorize('view', $coupon);

        $blueprint = Coupon::blueprint();
        $blueprint->setParent($coupon);

        [$values, $meta] = $this->extractFromFields($coupon, $blueprint);

        $viewData = [
            'title' => $coupon->code(),
            'reference' => $coupon->reference(),
            'actions' => [
                'save' => $coupon->updateUrl(),
            ],
            'values' => array_merge($values, [
                'id' => $coupon->id(),
                'redeemed_count' => $coupon->redeemedCount(),
            ]),
            'meta' => $meta,
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::current()->cant('update', $coupon),
            'breadcrumbs' => new Breadcrumbs([
                ['text' => __('Coupons'), 'url' => cp_route('simple-commerce.coupons.index')],
            ]),
            'itemActions' => Action::for($coupon, ['view' => 'form']),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Coupon created'));
        }

        return view('simple-commerce::cp.coupons.edit', array_merge($viewData, [
            'coupon' => $coupon,
        ]));
    }

    public function update(Request $request, $coupon)
    {
        $coupon = Coupon::find($coupon);

        $this->authorize('update', $coupon);

        $blueprint = Coupon::blueprint();

        $data = $request->except('id');

        $fields = $coupon
            ->blueprint()
            ->fields()
            ->addValues($data);

        $fields
            ->validator()
            ->withRules([
                'code' => [new UniqueCouponValue(except: $coupon->id())],
            ])
            ->withReplacements([
                'id' => $coupon->id(),
            ])
            ->validate();

        $values = $fields->process()->values();

        $coupon
            ->code($values->get('code'))
            ->type($values->get('type'))
            ->amount($values->get('amount'))
            ->merge($values->except(['code', 'type', 'amount']));

        $saved = $coupon->save();

        [$values] = $this->extractFromFields($coupon, $blueprint);

        return [
            'data' => array_merge((new CouponResource($coupon->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
            'saved' => $saved,
        ];
    }
}
