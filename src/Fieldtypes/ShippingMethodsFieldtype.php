<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Statamic\Fieldtypes\Relationship;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use Illuminate\Support\Facades\Config;
use Statamic\CP\Column;
use Statamic\Facades\Site;

class ShippingMethodsFieldtype extends Relationship
{
    protected $canSearch = false;

    protected $canCreate = false;

    public function getIndexItems($request)
    {
        $siteConfig = collect(Config::get('simple-commerce.sites'))
            ->get(Site::current()->handle());

        return collect($siteConfig['shipping']['methods'])
            ->map(function ($method) {
                return $this->toItemArray($method);
            })
            ->whereNotNull()
            ->toArray();
        
    }
     
    protected function toItemArray($id)
    {
        if (!class_exists($id)) {
            return null;
        }

        $instance = Shipping::use($id);

        return [
            'id'      => $id,
            'title'   => $instance->name(),
        ];
    }
 
    protected function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return collect($data)->map(function ($item) {
            return $this->toItemArray($item);
        });
    }

    public function rules(): array
    {
        if ($this->config('max_items') === 1) {
            return ['string'];
        }

        return parent::rules();
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        return $this->toItemArray($value);
    }
}
