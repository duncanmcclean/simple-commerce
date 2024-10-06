<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Orders\Calculator\Calculator;
use Illuminate\Support\Collection;
use Statamic\Support\Traits\FluentlyGetsAndSets;

trait Calculable
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
            ->getter(fn ($grandTotal) => $grandTotal ?? 0)
            ->args(func_get_args());
    }

    public function subTotal($subTotal = null)
    {
        return $this->fluentlyGetOrSet('subTotal')
            ->getter(fn ($subTotal) => $subTotal ?? 0)
            ->args(func_get_args());
    }

    public function discountTotal($couponTotal = null)
    {
        return $this->fluentlyGetOrSet('discountTotal')
            ->getter(fn ($discountTotal) => $discountTotal ?? 0)
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

    abstract function recalculate(): void;
}