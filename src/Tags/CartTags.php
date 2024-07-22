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
        if (! Cart::exists()) {
            return [];
        }

        return Cart::get()->toAugmentedArray();
    }

    public function has(): bool
    {
        return Cart::exists();
    }

    public function items()
    {
        if (! Cart::exists()) {
            return [];
        }

        $cart = Cart::get();

        return $cart->toAugmentedArray('items')['items']->value();
    }

    public function count()
    {
        if (! Cart::exists()) {
            return 0;
        }

        return Cart::get()->lineItems()->count();
    }

    public function quantityTotal()
    {
        if (! Cart::exists()) {
            return 0;
        }

        return Cart::get()->lineItems()->sum('quantity');
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
        if (Cart::exists()) {
            return Cart::get()->toAugmentedArray()['grand_total']->value();
        }

        return 0;
    }

    public function rawGrandTotal()
    {
        if (Cart::exists()) {
            return Cart::get()->grandTotal();
        }

        return 0;
    }

    public function subtotal()
    {
        if (Cart::exists()) {
            return Cart::get()->augmentedValue('sub_total');
        }

        return 0;
    }

    public function rawSubTotal()
    {
        if (Cart::exists()) {
            return Cart::get()->itemsTotal();
        }

        return 0;
    }

    public function subTotalWithTax()
    {
        if (Cart::exists()) {
            return Money::format(Cart::get()->subTotalWithTax(), Site::current());
        }

        return 0;
    }

    public function shippingTotal()
    {
        if (Cart::exists()) {
            return Cart::get()->toAugmentedArray()['shipping_total']->value();
        }

        return 0;
    }

    public function rawShippingTotal()
    {
        if (Cart::exists()) {
            return Cart::get()->shippingTotal();
        }

        return 0;
    }

    public function shippingTotalWithTax()
    {
        if (Cart::exists()) {
            return Money::format(Cart::get()->shippingTotalWithTax(), Site::current());
        }

        return 0;
    }

    public function taxTotal()
    {
        if (Cart::exists()) {
            return Cart::get()->toAugmentedArray()['tax_total']->value();
        }

        return 0;
    }

    public function rawTaxTotal()
    {
        if (Cart::exists()) {
            return Cart::get()->taxTotal();
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
        if (! Cart::exists()) {
            return collect();
        }

        return Cart::get()->lineItems()
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
        if (Cart::exists()) {
            return Cart::get()->toAugmentedArray()['coupon_total']->value();
        }

        return 0;
    }

    public function rawCouponTotal()
    {
        if (Cart::exists()) {
            return Cart::get()->couponTotal();
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
            $lineItemId = collect(Cart::get()->lineItems()->map->toArray())
                ->where('product', $product)
                ->when($this->params->get('variant'), function ($query, $variant) {
                    $query->where('variant', $variant);
                })
                ->pluck('id')
                ->first();
        }

        $lineItem = Cart::get()->lineItems()->find($lineItemId);

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
            $lineItemId = collect(Cart::get()->lineItems()->map->toArray())
                ->where('product', $product)
                ->when($this->params->get('variant'), function ($query, $variant) {
                    $query->where('variant', $variant);
                })
                ->pluck('id')
                ->first();
        }

        $lineItem = Cart::get()->lineItems()->find($lineItemId);

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
        $cart = Cart::get();

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
        if (! Cart::exists()) {
            return false;
        }

        return Cart::get()->lineItems()
            ->where('product', Product::find($this->params->get('product')))
            ->where('variant', $this->params->get('variant'))
            ->count() >= 1;
    }

    public function wildcard($method)
    {
        if (! Cart::exists()) {
            return null;
        }

        $cart = Cart::get();

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
