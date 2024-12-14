<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Orders\LineItems;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Facades\Statamic\Fields\FieldtypeRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Facades\Site;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;

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
            'total' => Money::format($order->shippingTotal(), $order->site()),
            'tracking_number' => $order->get('tracking_number'),
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