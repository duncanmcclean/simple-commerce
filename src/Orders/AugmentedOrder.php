<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Support\Money;
use Illuminate\Support\Collection;
use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Site;

class AugmentedOrder extends AbstractAugmented
{
    private $cachedKeys;

    public function keys(): array
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = $this->data->data()->keys()
            ->merge($this->data->supplements()->keys())
            ->merge($this->commonKeys())
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        $keys = [
            'id',
            'is_free',
            'customer',
            'coupon',
            'shipping_method',
            'shipping_option',
            'payment_gateway',
            'tax_breakdown',
        ];

        if ($this->data instanceof Order) {
            $keys = [
                ...$keys,
                'order_number',
                'date',
                'status',
            ];
        }

        return $keys;
    }

    public function coupon()
    {
        if (! $this->data->coupon()) {
            return null;
        }

        return $this->data->coupon()->toShallowAugmentedArray();
    }

    public function shippingMethod()
    {
        if (! $this->data->shippingMethod()) {
            return null;
        }

        return [
            'name' => $this->data->shippingMethod()->name(),
            'handle' => $this->data->shippingMethod()->handle(),
        ];
    }

    public function shippingOption()
    {
        if (! $this->data->shippingOption()) {
            return null;
        }

        return $this->data->shippingOption()->toAugmentedArray();
    }

    public function paymentGateway()
    {
        if (! $this->data->paymentGateway()) {
            return null;
        }

        return [
            'title' => $this->data->paymentGateway()->title(),
            'handle' => $this->data->paymentGateway()->handle(),
        ];
    }

    public function status()
    {
        if (! $this->data instanceof Order) {
            return null;
        }

        return $this->data->status()->value;
    }
}
