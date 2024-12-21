<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Taxes;

use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\CP\Breadcrumbs;
use Statamic\CP\Column;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class TaxZoneController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('manage taxes');

        $taxZones = TaxZone::all()->map(function ($taxZone) {
            return [
                'id' => $taxZone->handle(),
                'handle' => $taxZone->handle(),
                'name' => $taxZone->get('name'),
                'type' => match ($taxZone->get('type')) {
                    'countries' => __('Countries (:count)', ['count' => count($taxZone->get('countries', []))]),
                    'states' => __('States (:count)', ['count' => count($taxZone->get('states', []))]),
                    'postcodes' => __('Postcodes (:count)', ['count' => count($taxZone->get('postcodes', []))]),
                },
                'edit_url' => $taxZone->editUrl(),
                'delete_url' => $taxZone->deleteUrl(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $taxZones;
        }

        return view('simple-commerce::cp.tax-zones.index', [
            'taxZones' => $taxZones,
            'columns' => [
                Column::make('name')->label(__('Name')),
                Column::make('type')->label(__('Type')),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('manage taxes');

        $blueprint = TaxZone::blueprint();

        $fields = $blueprint->fields()->preProcess();
        $values = $fields->values();

        $viewData = [
            'title' => __('Create Tax Zone'),
            'actions' => [
                'save' => cp_route('simple-commerce.tax-zones.store'),
            ],
            'values' => $values->all(),
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
            'breadcrumbs' => new Breadcrumbs([
                ['text' => __('Tax Zones'), 'url' => cp_route('simple-commerce.tax-zones.index')],
            ]),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('simple-commerce::cp.tax-zones.create', $viewData);
    }

    public function store(Request $request)
    {
        $this->authorize('manage taxes');

        $blueprint = TaxZone::blueprint();

        $data = $request->all();

        $fields = $blueprint->fields()->addValues($data);

        $fields->validator()->validate();

        $values = $fields->process()->values();

        $taxZone = TaxZone::make()
            ->handle(Str::slug($values->get('name')))
            ->data($values->except('handle'));

        $saved = $taxZone->save();

        $fields = $blueprint->fields()
            ->setParent($taxZone)
            ->addValues($taxZone->data()->all())
            ->preProcess();

        return [
            'data' => [
                'id' => $taxZone->handle(),
                'title' => $taxZone->get('name'),
                'edit_url' => $taxZone->editUrl(),
                'values' => $fields->values()->all(),
            ],
            'saved' => $saved,
        ];
    }

    public function edit(Request $request, $taxZone)
    {
        $this->authorize('manage taxes');

        $taxZone = TaxZone::find($taxZone);

        $blueprint = TaxZone::blueprint();

        $values = $taxZone->data();

        $fields = $blueprint->fields()
            ->setParent($taxZone)
            ->addValues($values->all())
            ->preProcess();

        $viewData = [
            'title' => $taxZone->get('name'),
            'actions' => [
                'save' => $taxZone->updateUrl(),
            ],
            'values' => $fields->values()->all(),
            'meta' => $fields->meta()->all(),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::current()->cant('update', $taxZone),
            'breadcrumbs' => new Breadcrumbs([
                ['text' => __('Tax Zones'), 'url' => cp_route('simple-commerce.tax-zones.index')],
            ]),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Tax Zone created'));
        }

        return view('simple-commerce::cp.tax-zones.edit', array_merge($viewData, [
            'taxClass' => $taxZone,
        ]));
    }

    public function update(Request $request, $taxZone)
    {
        $this->authorize('manage taxes');

        $taxZone = TaxZone::find($taxZone);

        $blueprint = TaxZone::blueprint();

        $data = $request->except('handle');

        $fields = $blueprint->fields()->addValues($data);

        $fields->validator()->validate();

        $values = $fields->process()->values();

        $taxZone->merge($values);

        $saved = $taxZone->save();

        $fields = $blueprint->fields()
            ->setParent($taxZone)
            ->addValues($taxZone->data()->all())
            ->preProcess();

        return [
            'data' => [
                'id' => $taxZone->handle(),
                'title' => $taxZone->get('name'),
                'values' => $fields->values()->all(),
            ],
            'saved' => $saved,
        ];
    }

    public function destroy(Request $request, $taxZone)
    {
        $this->authorize('manage taxes');

        TaxZone::find($taxZone)->delete();

        return response('', 204);
    }
}
