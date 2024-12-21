<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\TaxClass;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Relationship;

class TaxClassFieldtype extends Relationship
{
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $selectable = false;
    protected $formComponent = 'tax-class-publish-form';
    protected $formStackSize = 'narrow';

    protected $formComponentProps = [
        'initialActions' => 'actions',
        'initialTitle' => 'title',
        'initialFieldset' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
    ];

    protected function toItemArray($id)
    {
        $taxClass = TaxClass::find($id);

        if (! $taxClass) {
            return $this->invalidItemArray($id);
        }

        return [
            'id' => $taxClass->handle(),
            'title' => $taxClass->get('name'),
            'edit_url' => $taxClass->editUrl(),
        ];
    }

    public function getIndexItems($request)
    {
        return TaxClass::all()->map(function ($taxClass) {
            return [
                'id' => $taxClass->handle(),
                'title' => $taxClass->get('name'),
            ];
        });
    }

    protected function augmentValue($value)
    {
        return TaxClass::find($value);
    }

    public function preProcessIndex($data)
    {
        return $this->getItemsForPreProcessIndex($data)->map(function ($item) {
            return [
                'id' => $item->handle(),
                'title' => $item->get('name'),
                'edit_url' => $item->editUrl(),
            ];
        });
    }

    protected function getCreatables()
    {
        $user = User::current();

        if (! $user->can('manage taxes')) {
            return [];
        }

        return [['url' => cp_route('simple-commerce.tax-classes.create')]];
    }

    protected function getCreateItemUrl()
    {
        return cp_route('simple-commerce.tax-classes.create');
    }

    public function rules(): array
    {
        return [
            function ($attribute, $value, $fail) {
                if (! TaxClass::find($value[0])) {
                    $fail(__('The selected tax class is invalid'));
                }
            },
        ];
    }
}
