<?php

namespace DoubleThreeDigital\SimpleCommerce\Helpers;

use DoubleThreeDigital\SimpleCommerce\Events\AddedToCart;
use DoubleThreeDigital\SimpleCommerce\Models\Cart as CartModel;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Statamic\Stache\Stache;
use Stripe\OrderItem;

class Cart
{
    public function create()
    {
        $cart = new CartModel();
        $cart->uuid = (new Stache())->generateId();
        $cart->save();

        return $cart->uuid;
    }

    public function exists(string $uuid)
    {
        if ($cart = CartModel::where('uuid', $uuid)->first()) {
            return true;
        }

        return false;
    }

    public function count(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        return CartItem::where('cart_id', $cart->id)->count();
    }

    public function get(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        return CartItem::with('cart', 'product', 'variant')
            ->where('cart_id', $cart->id)
            ->get();
    }

    public function add(string $uuid, array $data)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        $item = new CartItem();
        $item->uuid = (new Stache())->generateId();
        $item->product_id = Product::where('uuid', $data['product'])->first()->id;
        $item->variant_id = Variant::where('uuid', $data['variant'])->first()->id;
        $item->quantity = $data['quantity'];
        $item->cart_id = $cart->id;
        $item->save();

        if (! $this->alreadyShipping($uuid)) {
            $this->addShipping($uuid);
        }

        if (! $this->alreadyTax($uuid)) {
            $this->addTax($uuid);
        }

        event(new AddedToCart($cart, $item));

        return collect($cart->items);
    }

    public function remove(string $cartUuid, string $itemUuid)
    {
        $item = CartItem::where('uuid', $itemUuid)->first();
        $item->delete();

        return $this->get($cartUuid);
    }

    public function clear(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        CartItem::where('cart_id', $cart->id)
            ->each(function ($item) {
                $item->delete();
            });

        CartShipping::where('cart_id', $cart->id)
            ->each(function ($item) {
                $item->delete();
            });

        CartTax::where('cart_id', $cart->id)
            ->each(function ($item) {
                $item->delete();
            });

        $cart->delete();
    }

    public function total(string $uuid, string $type = null)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        if ($type === 'items') {
            return (new CartCalculator($cart))->itemsTotal()->total;
        }

        if ($type === 'shipping') {
            return (new CartCalculator($cart))->shippingTotal()->total;
        }

        if ($type === 'tax') {
            return (new CartCalculator($cart))->taxTotal()->total;
        }

        return $cart->total;
    }

    public function orderItems(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        return [
            'items' => CartItem::with('product', 'variant')->where('cart_id', $cart->id)->get(),
            'shipping' => CartShipping::with('shippingZone', 'shippingZone.country', 'shippingZone.state')->where('cart_id', $cart->id)->get(),
            'tax' => CartTax::with('taxRate', 'taxRate.country', 'taxRate.state')->where('cart_id', $cart->id)->get(),
            'totals' => [
                'overall' => (new Currency())->parse($this->total($uuid)),
                'items' => (new Currency())->parse($this->total($uuid, 'items')),
                'shipping' => (new Currency())->parse($this->total($uuid, 'shipping')),
                'tax' => (new Currency())->parse($this->total($uuid, 'tax')),
            ],
        ];
    }

    public function getShipping(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        return CartShipping::with('shippingZone', 'shippingZone.country', 'shippingZone.state')
            ->where('cart_id', $cart->id)
            ->get();
    }

    public function alreadyShipping(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        $shipping = CartShipping::where('cart_id', $cart->id)->get()->count();

        if ($shipping === 0) {
            return false;
        }

        return true;
    }

    public function addShipping(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        // TODO: work out a better shipping zone thing to add rather than just the first one

        $zone = ShippingZone::first();

        $shipping = new CartShipping();
        $shipping->uuid = (new Stache())->generateId();
        $shipping->shipping_zone_id = $zone->id;
        $shipping->cart_id = $cart->id;
        $shipping->save();

        return $shipping;
    }

    public function getTax(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        return CartTax::with('taxRate', 'taxRate.country', 'taxRate.state')
            ->where('cart_id', $cart->id)
            ->get();
    }

    public function alreadyTax(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        $tax = CartTax::where('cart_id', $cart->id)->get()->count();

        if ($tax === 0) {
            return false;
        }

        return true;
    }

    public function addTax(string $uuid)
    {
        $cart = CartModel::where('uuid', $uuid)->first();

        // TODO: work out a better shipping zone thing to add rather than just the first one

        $rate = TaxRate::first();

        $tax = new CartTax();
        $tax->uuid = (new Stache())->generateId();
        $tax->tax_rate_id = $rate->id;
        $tax->cart_id = $cart->id;
        $tax->save();

        return $tax;
    }
}
