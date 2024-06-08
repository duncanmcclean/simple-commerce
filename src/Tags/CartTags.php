<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Currency;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Facades\Site;

class CartTags extends SubTag
{
    use CartDriver;
    use Concerns\FormBuilder;

    public function index()
    {
        if ($this->hasCart()) {
            return $this->getOrMakeCart()->toAugmentedArray();
        }

        return [];
    }

    public function has()
    {
        return $this->hasCart();
    }

    public function items()
    {
        if ($this->hasCart()) {
            $cart = $this->getOrMakeCart();

            return $cart->toAugmentedArray('items')['items']->value();
        }

        return [];
    }

    public function count()
    {
        if ($this->hasCart()) {
            return $this->getCart()->lineItems()->count();
        }

        return 0;
    }

    public function quantityTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->lineItems()->sum('quantity');
        }

        return 0;
    }

    public function total()
    {
        return $this->grandTotal();
    }

    public function free()
    {
        return $this->rawGrandTotal() === 0;
    }

    public function grandTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['grand_total']->value();
        }

        return 0;
    }

    public function rawGrandTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->grandTotal();
        }

        return 0;
    }

    public function itemsTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['items_total']->value();
        }

        return 0;
    }

    public function rawItemsTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->itemsTotal();
        }

        return 0;
    }

    public function itemsTotalWithTax()
    {
        if ($this->hasCart()) {
            return Currency::parse($this->getCart()->itemsTotalWithTax(), Site::current());
        }

        return 0;
    }

    public function shippingTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['shipping_total']->value();
        }

        return 0;
    }

    public function rawShippingTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->shippingTotal();
        }

        return 0;
    }

    public function shippingTotalWithTax()
    {
        if ($this->hasCart()) {
            return Currency::parse($this->getCart()->shippingTotalWithTax(), Site::current());
        }

        return 0;
    }

    public function taxTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['tax_total']->value();
        }

        return 0;
    }

    public function rawTaxTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->taxTotal();
        }

        return 0;
    }

    public function taxTotalSplit(): Collection
    {
        return $this->rawTaxTotalSplit()->map(function ($tax) {
            $tax['amount'] = Currency::parse($tax['amount'], Site::current());

            return $tax;
        });
    }

    public function rawTaxTotalSplit(): Collection
    {
        if ($this->hasCart()) {
            return $this->getCart()->lineItems()
                ->groupBy(fn ($lineItem) => $lineItem->tax()['rate'])
                ->map(function ($group, $rate) {
                    return [
                        'rate' => $rate,
                        'amount' => $group->sum(fn ($lineItem) => $lineItem->tax()['amount']),
                    ];
                })
                ->values();
        }

        return collect();
    }

    public function couponTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['coupon_total']->value();
        }

        return 0;
    }

    public function rawCouponTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->couponTotal();
        }

        return 0;
    }

    public function addItem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart-items.store'),
            [],
            'POST'
        );
    }

    public function updateItem()
    {
        $lineItemId = $this->params->get('item');

        if ($product = $this->params->get('product')) {
            $lineItemId = collect($this->getCart()->lineItems()->map->toArray())
                ->where('product', $product)
                ->when($this->params->get('variant'), function ($query, $variant) {
                    $query->where('variant', $variant);
                })
                ->pluck('id')
                ->first();
        }

        $lineItem = $this->getCart()->lineItem($lineItemId);

        return $this->createForm(
            route('statamic.simple-commerce.cart-items.update', [
                'item' => $lineItemId,
            ]),
            optional($lineItem)->toArray() ?? [],
            'POST',
            ['product', 'item']
        );
    }

    public function removeItem()
    {
        $lineItemId = $this->params->get('item');

        if ($product = $this->params->get('product')) {
            $lineItemId = collect($this->getCart()->lineItems()->map->toArray())
                ->where('product', $product)
                ->when($this->params->get('variant'), function ($query, $variant) {
                    $query->where('variant', $variant);
                })
                ->pluck('id')
                ->first();
        }

        $lineItem = $this->getCart()->lineItem($lineItemId);

        return $this->createForm(
            route('statamic.simple-commerce.cart-items.destroy', [
                'item' => $lineItemId,
            ]),
            optional($lineItem)->toArray() ?? [],
            'DELETE',
            ['product', 'item']
        );
    }

    public function update()
    {
        $cart = $this->getCart();

        return $this->createForm(
            route('statamic.simple-commerce.cart.update'),
            $cart->toAugmentedArray(),
            'POST'
        );
    }

    public function empty()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart.empty'),
            [],
            'DELETE'
        );
    }

    public function alreadyExists()
    {
        if ($this->hasCart()) {
            return $this->getCart()->lineItems()
                ->where('product', Product::find($this->params->get('product')))
                ->where('variant', $this->params->get('variant'))
                ->count() >= 1;
        }

        return false;
    }

    public function wildcard($method)
    {
        if (! $this->hasCart()) {
            return null;
        }

        $cart = $this->getCart();

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        $camelCaseMethod = Str::camel($method);

        if ($camelCaseMethod != $method && method_exists($this, $camelCaseMethod)) {
            return $this->{$camelCaseMethod}();
        }

        if (property_exists($cart, $method)) {
            return $cart->{$method};
        }

        if (array_key_exists($method, $cart->toAugmentedArray())) {
            return $cart->toAugmentedArray()[$method];
        }

        if ($cart->has($method)) {
            return $cart->get($method);
        }

        return null;
    }
}
