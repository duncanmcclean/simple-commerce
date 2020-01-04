<?php

namespace Damcclean\Commerce\Helpers;

use Damcclean\Commerce\Models\Cart as CartModel;
use Damcclean\Commerce\Models\CartItem;
use Statamic\Stache\Stache;

class Cart
{
    public function create()
    {
        $cart = new CartModel();
        $cart->uid = (new Stache())->generateId();
        $cart->total = 00.00;
        $cart->save();

        return $cart->uid;
    }

    public function exists($uid)
    {
        if ($cart = CartModel::where('uid', $uid)->first()) {
            return true;
        }

        return false;
    }

    public function count($uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        return collect($cart->items)->count();
    }

    public function get($uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        return collect($cart->items);
    }

    public function add($uid, $data)
    {
        $cart = CartModel::where('uid', $uid)->first();

        $item = new CartItem();
        $item->uid = (new Stache())->generateId();
        $item->product_id = $data['product'];
        $item->variant_id = $data['variant'];
        $item->quantity = $data['quantity'];
        $item->cart_id = $cart->id;
        $item->save();

        return collect($cart->items);
    }

    public function remove($uid, $itemUid)
    {
        $item = CartItem::where('uid', $itemUid)->first();

        $item->delete();

        return (Cart::where('uid', $uid)->first())->items;
    }

    public function clear($uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        collect($cart->items)
            ->each(function ($item) {
                $item->delete();
            });

        $cart->delete();
    }
}
