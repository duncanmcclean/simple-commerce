<?php

namespace DoubleThreeDigital\SimpleCommerce\Helpers;

use DoubleThreeDigital\SimpleCommerce\Events\AddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\RemovedFromCart;
use DoubleThreeDigital\SimpleCommerce\Events\ShippingAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\TaxAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Models\Cart as CartModel;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Support\Facades\Event;
use Statamic\Stache\Stache;

class Cart
{
    public function create()
    {
        $cart = CartModel::create([
            'uuid' => (new Stache())->generateId(),
        ]);

        return $cart->uuid;
    }

    public function exists(string $uuid)
    {
        if (CartModel::where('uuid', $uuid)->first()) {
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

        $item = CartItem::create([
            'uuid' => (new Stache())->generateId(),
            'product_id' => Product::where('uuid', $data['product'])->first()->id,
            'variant_id' => Variant::where('uuid', $data['variant'])->first()->id,
            'quantity' => $data['quantity'],
            'cart_id' => $cart->id,
        ]);

        if (! $this->alreadyShipping($uuid)) {
            $this->addShipping($uuid);
        }

        if (! $this->alreadyTax($uuid)) {
            $this->addTax($uuid);
        }

        Event::dispatch(new AddedToCart($cart, $item, $item->variant));

        return collect($cart->items);
    }

    public function remove(string $cartUuid, string $itemUuid)
    {
        $cart = CartModel::where('uuid', $cartUuid)->first();

        $item = CartItem::where('uuid', $itemUuid)->first();

        Event::dispatch(new RemovedFromCart($cart, $item->variant));

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

        Event::dispatch(new ShippingAddedToCart($cart, $shipping, $zone));

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

        Event::dispatch(new TaxAddedToCart($cart, $tax, $rate));

        return $tax;
    }
}
