<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Support\Money;
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

        return $this->cachedKeys = $this->data->keys()
            ->merge($this->commonKeys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'order_number',
            'status',
            'customer',
            'line_items',
            'grand_total',
            'sub_total',
            'discount_total',
            'tax_total',
            'shipping_total',
            'payment_gateway',
            'payment_data',
            'shipping_method',
        ];
    }

    // todo: status

    public function customer()
    {
        $customer = $this->data->customer();

        if ($customer instanceof GuestCustomer) {
            return $customer->toArray();
        }

        return $customer->toAugmentedCollection();
    }

    public function grandTotal(): string
    {
        return Money::format($this->data->grandTotal(), Site::selected());
    }

    public function subTotal(): string
    {
        return Money::format($this->data->subTotal(), Site::selected());
    }

    public function discountTotal(): string
    {
        return Money::format($this->data->discountTotal(), Site::selected());
    }

    public function taxTotal(): string
    {
        return Money::format($this->data->taxTotal(), Site::selected());
    }

    public function shippingTotal(): string
    {
        return Money::format($this->data->shippingTotal(), Site::selected());
    }

    public function lineItems()
    {
        // TODO: Refactor into an AugmentedLineItem class
        return $this->data->lineItems()->map(function ($lineItem) {
            return array_merge($lineItem->toArray(), [
                'product' => $lineItem->product()->toAugmentedArray(),
            ]);
        });
    }
}
