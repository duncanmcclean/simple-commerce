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

        return [
            'has_shipping_method' => $order->shippingMethod() ? true : false,
            'name' => $order->shippingMethod()?->name(),
            'logo' => $order->shippingMethod()?->logo(),
            'total' => Money::format($order->shippingTotal(), $order->site()),
        ];
    }

    public function process($data): null
    {
        return null;
    }
}