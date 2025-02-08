<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;
use Statamic\Fieldtypes\Relationship;

class PaymentGatewayFieldtype extends Relationship
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

    public function preProcessIndex($data)
    {
        return collect($data)->map(function ($item) {
            $paymentGateway = PaymentGateway::find($item);

            return $paymentGateway->title();
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
