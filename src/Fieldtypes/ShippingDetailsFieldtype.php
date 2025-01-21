<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Support\Money;
use Statamic\Fields\Fieldtype;

class ShippingDetailsFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preProcess($data)
    {
        $order = $this->field->parent();

        if (! $order->shippingOption()) {
            return ['has_shipping_option' => false];
        }

        return [
            'has_shipping_option' => true,
            'name' => $order->shippingOption()->name(),
            'handle' => $order->shippingOption()->handle(),
            'details' => array_filter([
                __('Amount') => Money::format($order->shippingTotal(), $order->site()),
                __('Tracking Number') => $order->get('tracking_number'),
            ]),
            'shipping_method' => [
                'name' => $order->shippingMethod()->name(),
                'handle' => $order->shippingMethod()->handle(),
                'logo' => $order->shippingMethod()->logo(),
            ],
        ];
    }

    public function process($data): null
    {
        return null;
    }
}
