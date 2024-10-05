<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DuncanMcClean\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DuncanMcClean\SimpleCommerce\Http\Resources\API\CartResource;
use DuncanMcClean\SimpleCommerce\Orders\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        if (! $cart->customer()) {
            throw ValidationException::withMessages([
                'customer' => __("Order cannot be created without customer information."),
            ]);
        }

        // TODO: Refactor when I want to.
        if ($coupon = $request->coupon) {
            $coupon = Coupon::findByCode($coupon);

            if (! $coupon->isValid($cart)) {
                throw ValidationException::withMessages([
                    'coupon' => __("This coupon isn't valid for this cart."),
                ]);
            }

            $cart->set('coupon', $coupon);
        }

        $cart->merge($validated);
        $cart->recalculate()->save();

        $order = Order::makeFromCart($cart);
        $order->save();

        Cart::forgetCurrentCart();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart);
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }
}
