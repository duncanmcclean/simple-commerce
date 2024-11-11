<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use Statamic\Fields\Value;

class CartShippingController
{
    public function __invoke()
    {
        $cart = Cart::current();

        return ShippingMethod::all()
            ->filter(fn ($shippingMethod) => in_array(
                $shippingMethod->handle(),
                config('statamic.simple-commerce.shipping.methods')
            ))
            ->filter->isAvailable($cart)
            ->map(fn ($shippingMethod) => [
                'name' => $shippingMethod->name(),
                'handle' => $shippingMethod->handle(),
                'cost' => new Value(
                    value: fn () => $shippingMethod->cost($cart),
                    handle: 'cost',
                    fieldtype: new MoneyFieldtype,
                ),
            ])
            ->values()
            ->all();
    }
}