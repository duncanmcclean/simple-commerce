<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;
use Statamic\Fieldtypes\Relationship;

class ShippingMethodFieldtype extends Relationship
{
    protected $selectable = false;

    protected function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
    }

    public function getIndexItems($request)
    {
        // TODO: Implement getIndexItems() method.
    }

    public function augment($values)
    {
        if ($this->config('max_items') == 1) {
            $shippingMethod = ShippingMethod::find($values);

            return [
                'name' => $shippingMethod->name(),
                'handle' => $shippingMethod->handle(),
            ];
        }

        return collect($values)->map(function (string $handle) {
            $shippingMethod = ShippingMethod::find($handle);

            return [
                'name' => $shippingMethod->name(),
                'handle' => $shippingMethod->handle(),
            ];
        })->filter()->all();
    }

    public function preProcessIndex($data)
    {
        return collect($data)->map(function ($item) {
            $shippingMethod = ShippingMethod::find($item);

            return [
                'id' => $shippingMethod->handle(),
                'title' => $shippingMethod->name(),
            ];
        });
    }
}