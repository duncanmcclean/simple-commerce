<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use Statamic\Fields\Fieldtype;

class PaymentStatusFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        return [
            'statuses' => collect(PaymentStatus::cases())
                ->mapWithKeys(fn ($case) => [$case->value => $case->name])
                ->toArray(),
        ];
    }

    public function preProcess($data)
    {
        if (! $data) {
            return PaymentStatus::Unpaid;
        }

        return $data;
    }

    public function process($data)
    {
        return $data;
    }

    public static function title()
    {
        return __('Payment Status');
    }

    public function component(): string
    {
        return 'payment-status';
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
