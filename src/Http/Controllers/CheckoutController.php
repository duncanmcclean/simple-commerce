<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DuncanMcClean\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DuncanMcClean\SimpleCommerce\Http\Resources\API\CartResource;
use DuncanMcClean\SimpleCommerce\Orders\Blueprint;
use Illuminate\Http\Request;

class CheckoutController
{
    use Concerns\HandlesCustomerInformation, Concerns\ValidatesStock;

    public function __invoke(Request $request)
    {
        $cart = Cart::current();
        $values = $request->all();

        // TODO: This is dumb. Find a better way.
        if (isset($values['use_shipping_address_for_billing'])) {
            $values['use_shipping_address_for_billing'] = $values['use_shipping_address_for_billing'] === 'on';
        }

        $validated = Order::blueprint()->fields()->addValues($values)->validate();

        // TODO: handle this better, instead of one exception per product, collect them all and return them all
        $cart->lineItems()->each(function ($lineItem) use ($request, $cart) {
            $this->validateStock($request, $cart, $lineItem);
        });

        $cart = $this->handleCustomerInformation($request, $cart);

        $cart->merge($validated);
        $cart->save();

        $order = Order::makeFromCart($cart);
        $order->save();

        Cart::forgetCurrentCart();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart);
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }
}
