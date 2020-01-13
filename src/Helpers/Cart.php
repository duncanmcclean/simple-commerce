<?php

namespace Damcclean\Commerce\Helpers;

use Damcclean\Commerce\Events\AddedToCart;
use Damcclean\Commerce\Models\Cart as CartModel;
use Damcclean\Commerce\Models\CartItem;
use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\Variant;
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

        return CartItem::where('cart_id', $cart->id)
            ->with('variant')
            ->with('product')
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

        $cart->total += (Variant::where('uid', $data['variant'])->first()->price) * $data['quantity'];
        $cart->save();

        event(new AddedToCart($cart, $item));

        return collect($cart->items);
    }

    public function remove(string $cartUid, string $itemUid)
    {
        $cart = CartModel::where('uid', $cartUid)->first();

        $item = CartItem::where('uid', $itemUid)->first();
        $item->delete();

        // TODO: actually remove the amount from the cart total

        return CartItem::where('cart_id', $cart->id)->get();
    }

    public function clear(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        CartItem::where('cart_id', $cart->id)
            ->each(function ($item) {
                $item->delete();
            });

        $cart->delete();
    }

    public function total(string $uid)
    {
        $cart = CartModel::where('uid', $uid)->first();

        return number_format($cart->total, 2);
    }
}
