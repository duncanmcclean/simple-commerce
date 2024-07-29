<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Facades\Site;

class CartTags extends SubTag
{
    use Concerns\FormBuilder;

    public function index()
    {
        if (! Cart::hasCurrentCart()) {
            return [];
        }

        return Cart::current()->toAugmentedArray();
    }

    public function has(): bool
    {
        return Cart::hasCurrentCart();
    }

    public function items()
    {
        if (! Cart::hasCurrentCart()) {
            return [];
        }

        $cart = Cart::current();

        return $cart->augmentedValue('line_items');
    }

    public function count()
    {
        if (! Cart::hasCurrentCart()) {
            return 0;
        }

        return Cart::current()->lineItems()->count();
    }

    public function quantityTotal()
    {
        if (! Cart::hasCurrentCart()) {
            return 0;
        }

        return Cart::current()->lineItems()->sum('quantity');
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
        if (Cart::hasCurrentCart()) {
            return Cart::current()->toAugmentedArray()['grand_total']->value();
        }

        return 0;
    }

    public function rawGrandTotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->grandTotal();
        }

        return 0;
    }

    public function subtotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->augmentedValue('sub_total');
        }

        return 0;
    }

    public function rawSubTotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->itemsTotal();
        }

        return 0;
    }

    public function subTotalWithTax()
    {
        if (Cart::hasCurrentCart()) {
            return Money::format(Cart::current()->subTotalWithTax(), Site::current());
        }

        return 0;
    }

    public function shippingTotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->toAugmentedArray()['shipping_total']->value();
        }

        return 0;
    }

    public function rawShippingTotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->shippingTotal();
        }

        return 0;
    }

    public function shippingTotalWithTax()
    {
        if (Cart::hasCurrentCart()) {
            return Money::format(Cart::current()->shippingTotalWithTax(), Site::current());
        }

        return 0;
    }

    public function taxTotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->toAugmentedArray()['tax_total']->value();
        }

        return 0;
    }

    public function rawTaxTotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->taxTotal();
        }

        return 0;
    }

    public function taxTotalSplit(): Collection
    {
        return $this->rawTaxTotalSplit()->map(function ($tax) {
            $tax['amount'] = Money::format($tax['amount'], Site::current());

            return $tax;
        });
    }

    public function rawTaxTotalSplit(): Collection
    {
        if (! Cart::hasCurrentCart()) {
            return collect();
        }

        return Cart::current()->lineItems()
            ->groupBy(fn ($lineItem) => $lineItem->tax()['rate'])
            ->map(function ($group, $rate) {
                return [
                    'rate' => $rate,
                    'amount' => $group->sum(fn ($lineItem) => $lineItem->tax()['amount']),
                ];
            })
            ->values();
    }

    public function couponTotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->toAugmentedArray()['coupon_total']->value();
        }

        return 0;
    }

    public function rawCouponTotal()
    {
        if (Cart::hasCurrentCart()) {
            return Cart::current()->couponTotal();
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
            $lineItemId = collect(Cart::current()->lineItems()->map->toArray())
                ->where('product', $product)
                ->when($this->params->get('variant'), function ($query, $variant) {
                    $query->where('variant', $variant);
                })
                ->pluck('id')
                ->first();
        }

        $lineItem = Cart::current()->lineItems()->find($lineItemId);

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
            $lineItemId = collect(Cart::current()->lineItems()->map->toArray())
                ->where('product', $product)
                ->when($this->params->get('variant'), function ($query, $variant) {
                    $query->where('variant', $variant);
                })
                ->pluck('id')
                ->first();
        }

        $lineItem = Cart::current()->lineItems()->find($lineItemId);

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
        $cart = Cart::current();

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
        if (! Cart::hasCurrentCart()) {
            return false;
        }

        return Cart::current()->lineItems()
            ->where('product', Product::find($this->params->get('product')))
            ->where('variant', $this->params->get('variant'))
            ->count() >= 1;
    }

    public function wildcard($method)
    {
        if (! Cart::hasCurrentCart()) {
            return null;
        }

        $cart = Cart::current();

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
