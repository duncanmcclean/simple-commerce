<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Illuminate\Support\Collection;
use Statamic\Support\Traits\FluentlyGetsAndSets;

trait Calculable
{
    use FluentlyGetsAndSets;

    protected $grandTotal;
    protected $subTotal;
    protected $couponTotal;
    protected $taxTotal;
    protected $shippingTotal;

    public function grandTotal($grandTotal = null)
    {
        return $this->fluentlyGetOrSet('grandTotal')
            ->getter(fn ($grandTotal) => $grandTotal ?? 0)
            ->args(func_get_args());
    }

    public function subTotal($subTotal = null)
    {
        return $this->fluentlyGetOrSet('subTotal')
            ->getter(fn ($subTotal) => $subTotal ?? 0)
            ->args(func_get_args());
    }

    public function couponTotal($couponTotal = null)
    {
        return $this->fluentlyGetOrSet('couponTotal')
            ->getter(fn ($couponTotal) => $couponTotal ?? 0)
            ->args(func_get_args());
    }

    public function taxTotal($taxTotal = null)
    {
        return $this->fluentlyGetOrSet('taxTotal')
            ->getter(fn ($taxTotal) => $taxTotal ?? 0)
            ->args(func_get_args());
    }

    public function taxTotals(): Collection
    {
        return $this->lineItems()
            ->groupBy(fn (LineItem $lineItem) => $lineItem->get('tax_rate'))
            ->map(fn ($group, $taxRate) => [
                'rate' => $taxRate,
                'amount' => $group->sum->get('tax_total'),
            ])
            ->values();
    }

    public function shippingTotal($shippingTotal = null)
    {
        return $this->fluentlyGetOrSet('shippingTotal')
            ->getter(fn ($shippingTotal) => $shippingTotal ?? 0)
            ->args(func_get_args());
    }

    public function recalculate(): self
    {
        // TODO

        return $this;
    }
}