<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

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

    public function isFree(): bool
    {
        return $this->grandTotal() === 0;
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

    public function taxBreakdown(): array
    {
        return collect()
            ->merge($this->lineItems()->flatMap->get('tax_breakdown')->all())
            ->merge($this->get('shipping_tax_breakdown'))
            ->groupBy(fn ($tax) => $tax['rate'].$tax['description'].$tax['zone'])
            ->map(fn ($group) => [
                'rate' => $group->first()['rate'],
                'description' => $group->first()['description'],
                'zone' => $group->first()['zone'],
                'amount' => $group->sum('amount'),
            ])
            ->values()
            ->all();
    }

    public function shippingTotal($shippingTotal = null)
    {
        return $this->fluentlyGetOrSet('shippingTotal')
            ->getter(fn ($shippingTotal) => (int) $shippingTotal ?? 0)
            ->args(func_get_args());
    }
}
