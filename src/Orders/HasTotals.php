<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Illuminate\Support\Collection;
use Statamic\Support\Traits\FluentlyGetsAndSets;

trait HasTotals
{
    use FluentlyGetsAndSets;

    protected $grandTotal;
    protected $subTotal;
    protected $discountTotal;
    protected $taxTotal;
    protected $shippingTotal;

    public function grandTotal($grandTotal = null)
    {
        return $this->fluentlyGetOrSet('grandTotal')
            ->getter(fn ($grandTotal) => (int) $grandTotal ?? 0)
            ->args(func_get_args());
    }

    public function subTotal($subTotal = null)
    {
        return $this->fluentlyGetOrSet('subTotal')
            ->getter(fn ($subTotal) => (int) $subTotal ?? 0)
            ->args(func_get_args());
    }

    public function discountTotal($couponTotal = null)
    {
        return $this->fluentlyGetOrSet('discountTotal')
            ->getter(fn ($discountTotal) => (int) $discountTotal ?? 0)
            ->args(func_get_args());
    }

    public function taxTotal($taxTotal = null)
    {
        return $this->fluentlyGetOrSet('taxTotal')
            ->getter(fn ($taxTotal) => (int) $taxTotal ?? 0)
            ->args(func_get_args());
    }

    public function taxTotals(): Collection
    {
        // todo: add in shipping taxes here too
        return $this->lineItems()
            ->groupBy(fn (LineItem $lineItem) => $lineItem->get('tax_rate'))
            ->map(fn ($group, $taxRate) => [
                'rate' => $taxRate,
                'amount' => (int) $group->sum->get('tax_total'),
            ])
            ->values();
    }

    public function shippingTotal($shippingTotal = null)
    {
        return $this->fluentlyGetOrSet('shippingTotal')
            ->getter(fn ($shippingTotal) => (int) $shippingTotal ?? 0)
            ->args(func_get_args());
    }
}