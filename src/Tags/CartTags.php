<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;

class CartTags extends SubTag
{
    use Concerns\FormBuilder;
    use CartDriver;

    public function index()
    {
        return $this->getOrMakeCart()->toAugmentedArray();
    }

    public function has()
    {
        return $this->hasCart();
    }

    public function items()
    {
        $cart = $this->getOrMakeCart();

        return collect($cart->toAugmentedArray()['items']->value())->map->toArray();
    }

    public function count()
    {
        if (! $this->hasCart()) {
            return 0;
        }

        return $this->getCart()->lineItems()->count();
    }

    public function quantityTotal()
    {
        if (! $this->hasCart()) {
            return 0;
        }

        return $this->getCart()->lineItems()->sum('quantity');
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
            'POST'
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
            'DELETE'
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
        return $this->getCart()->lineItems()
            ->where('product', Product::find($this->params->get('product')))
            ->where('variant', $this->params->get('variant'))
            ->count() >= 1;
    }

    public function wildcard($method)
    {
        $cart = $this->getCart();

        if (method_exists($this, $method)) {
            return $this->{$method}();
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
