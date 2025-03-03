<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

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
            'details' => $order->shippingMethod()->fieldtypeDetails($order),
            'shipping_method' => [
                'name' => $order->shippingMethod()->title(),
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
