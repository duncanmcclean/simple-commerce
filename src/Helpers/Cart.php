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

class Cart
{
    public function create()
    {
        $cart = new CartModel();
        $cart->uid = (new Stache())->generateId();
        $cart->save();

        return $cart->uid;
    }

    public function exists(string $uid)
    {
        if ($cart = CartModel::where('uid', $uid)->first()) {
            return true;
        }

        return false;
    }

    public function count(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        return CartItem::where('cart_id', $cart->id)->count();
    }

    public function get(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        return CartItem::with('cart', 'product', 'variant')
            ->where('cart_id', $cart->id)
            ->get();
    }

    public function add(string $uid, array $data)
    {
        $cart = CartModel::where('uid', $uid)->first();

        $item = new CartItem();
        $item->uid = (new Stache())->generateId();
        $item->product_id = Product::where('uid', $data['product'])->first()->id;
        $item->variant_id = Variant::where('uid', $data['variant'])->first()->id;
        $item->quantity = $data['quantity'];
        $item->cart_id = $cart->id;
        $item->save();

        if (! $this->alreadyShipping($uid)) {
            $this->addShipping($uid);
        }

        if (! $this->alreadyTax($uid)) {
            $this->addTax($uid);
        }

        event(new AddedToCart($cart, $item));

        return collect($cart->items);
    }

    public function remove(string $cartUid, string $itemUid)
    {
        $item = CartItem::where('uid', $itemUid)->first();
        $item->delete();

        return $this->get($cartUid);
    }

    public function clear(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

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

    public function total(string $uid, string $type = null)
    {
        $cart = CartModel::where('uid', $uid)->first();

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

    public function getShipping(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        return CartShipping::with('shippingZone', 'shippingZone.country', 'shippingZone.state')
            ->where('cart_id', $cart->id)
            ->get();
    }

    public function alreadyShipping(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        $shipping = CartShipping::where('cart_id', $cart->id)->get()->count();

        if ($shipping === 0) {
            return false;
        }

        return true;
    }

    public function addShipping(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        // TODO: work out a better shipping zone thing to add rather than just the first one

        $zone = ShippingZone::first();

        $shipping = new CartShipping();
        $shipping->uid = (new Stache())->generateId();
        $shipping->shipping_zone_id = $zone->id;
        $shipping->cart_id = $cart->id;
        $shipping->save();

        return $shipping;
    }

    public function getTax(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        return CartTax::with('taxRate', 'taxRate.country', 'taxRate.state')
            ->where('cart_id', $cart->id)
            ->get();
    }

    public function alreadyTax(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        $tax = CartTax::where('cart_id', $cart->id)->get()->count();

        if ($tax === 0) {
            return false;
        }

        return true;
    }

    public function addTax(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        // TODO: work out a better shipping zone thing to add rather than just the first one

        $rate = TaxRate::first();

        $tax = new CartTax();
        $tax->uid = (new Stache())->generateId();
        $tax->tax_rate_id = $rate->id;
        $tax->cart_id = $cart->id;
        $tax->save();

        return $tax;
    }
}
