<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;
use Statamic\Fieldtypes\Relationship;

class ShippingMethodFieldtype extends Relationship
{
    protected $selectable = false;
    protected $indexComponent = null;

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
        if ($this->config('max_items') === 1) {
            $shippingMethod = ShippingMethod::find($values);

            return [
                'name' => $shippingMethod->title(),
                'handle' => $shippingMethod->handle(),
            ];
        }

        return collect($values)->map(function (string $handle) {
            $shippingMethod = ShippingMethod::find($handle);

            return [
                'name' => $shippingMethod->title(),
                'handle' => $shippingMethod->handle(),
            ];
        })->filter()->all();
    }

    public function preProcessIndex($data)
    {
        return collect($data)->map(function ($item) {
            $shippingMethod = ShippingMethod::find($item);

            return $shippingMethod->title();
        })->implode(', ');
    }

    public function rules(): array
    {
        if ($this->config('max_items') === 1) {
            return ['string'];
        }

        return parent::rules();
    }
}
