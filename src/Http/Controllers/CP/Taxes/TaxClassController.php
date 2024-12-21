<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Taxes;

use DuncanMcClean\SimpleCommerce\Facades\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\CP\Breadcrumbs;
use Statamic\CP\Column;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class TaxClassController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('manage taxes');

        $taxClasses = TaxClass::all()->map(function ($taxClass) {
            return [
                'id' => $taxClass->handle(),
                'handle' => $taxClass->handle(),
                'name' => $taxClass->get('name'),
                'edit_url' => $taxClass->editUrl(),
                'delete_url' => $taxClass->deleteUrl(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $taxClasses;
        }

        return view('simple-commerce::cp.tax-classes.index', [
            'taxClasses' => $taxClasses,
            'columns' => [
                Column::make('name')->label(__('Name')),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('manage taxes');

        $blueprint = TaxClass::blueprint();

        $fields = $blueprint->fields()->preProcess();
        $values = $fields->values();

        $viewData = [
            'title' => __('Create Tax Class'),
            'actions' => [
                'save' => cp_route('simple-commerce.tax-classes.store'),
            ],
            'values' => $values->all(),
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
            'breadcrumbs' => [
                ['text' => __('Tax Classes'), 'url' => cp_route('simple-commerce.tax-classes.index')],
            ],
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('simple-commerce::cp.tax-classes.create', $viewData);
    }

    public function store(Request $request)
    {
        $this->authorize('manage taxes');

        $blueprint = TaxClass::blueprint();

        $data = $request->all();

        $fields = $blueprint->fields()->addValues($data);

        $fields->validator()->validate();

        $values = $fields->process()->values();

        $taxClass = TaxClass::make()
            ->handle(Str::slug($values->get('name')))
            ->data($values->except('handle'));

        $saved = $taxClass->save();

        $fields = $blueprint->fields()
            ->setParent($taxClass)
            ->addValues($taxClass->data()->all())
            ->preProcess();

        return [
            'data' => [
                'id' => $taxClass->handle(),
                'title' => $taxClass->get('name'),
                'values' => $fields->values()->all(),
            ],
            'saved' => $saved,
            'redirect' => $taxClass->editUrl().'?created=true',
        ];
    }

    public function edit(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        $taxClass = TaxClass::find($taxClass);

        $blueprint = TaxClass::blueprint();

        $values = $taxClass->data();

        $fields = $blueprint->fields()
            ->setParent($taxClass)
            ->addValues($values->all())
            ->preProcess();

        $viewData = [
            'title' => $taxClass->get('name'),
            'actions' => [
                'save' => $taxClass->updateUrl(),
            ],
            'values' => $fields->values()->all(),
            'meta' => $fields->meta()->all(),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::current()->cant('update', $taxClass),
            'breadcrumbs' => new Breadcrumbs([
                ['text' => __('Tax Classes'), 'url' => cp_route('simple-commerce.tax-classes.index')],
            ]),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Tax Class created'));
        }

        return view('simple-commerce::cp.tax-classes.edit', array_merge($viewData, [
            'taxClass' => $taxClass,
        ]));
    }

    public function update(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        $taxClass = TaxClass::find($taxClass);

        $blueprint = TaxClass::blueprint();

        $data = $request->except('handle');

        $fields = $blueprint->fields()->addValues($data);

        $fields->validator()->validate();

        $values = $fields->process()->values();

        $taxClass->merge($values);

        $saved = $taxClass->save();

        $fields = $blueprint->fields()
            ->setParent($taxClass)
            ->addValues($taxClass->data()->all())
            ->preProcess();

        return [
            'data' => [
                'id' => $taxClass->handle(),
                'title' => $taxClass->get('name'),
                'values' => $fields->values()->all(),
            ],
            'saved' => $saved,
        ];
    }

    public function destroy(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        TaxClass::find($taxClass)->delete();

        return response('', 204);
    }
}
