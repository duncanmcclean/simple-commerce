<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Statamic\Fields\Fieldtype;

class OrderStatusFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        return [
            'statuses' => collect(OrderStatus::cases())
                ->mapWithKeys(fn ($case) => [$case->value => $case->name])
                ->toArray(),
        ];
    }

    public function preProcess($data)
    {
        if (! $data) {
            return OrderStatus::Cart;
        }

        return $data;
    }

    public function process($data)
    {
        return $data;
    }

    public static function title()
    {
        return __('Order Status');
    }

    public function component(): string
    {
        return 'order-status';
    }

    public function augment($value)
    {
        return $value;
    }

    public static function docsUrl()
    {
        return 'https://simple-commerce.duncanmcclean.com/carts-and-orders';
    }

    public function preProcessIndex($value)
    {
        return $value;
    }
}
