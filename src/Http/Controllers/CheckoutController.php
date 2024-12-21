<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Events\CouponRedeemed;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Http\Resources\API\CartResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController
{
    use Concerns\HandlesCustomerInformation, Concerns\RedeemsCoupons, Concerns\ValidatesStock;

    public function __invoke(Request $request)
    {
        $cart = Cart::current();
        $values = $request->all();

        // TODO: This is dumb. Find a better way.
        if (isset($values['use_shipping_address_for_billing'])) {
            $values['use_shipping_address_for_billing'] = $values['use_shipping_address_for_billing'] === 'on';
        }

        $validated = Order::blueprint()->fields()->except(['customer', 'coupon'])->addValues($values)->validate();

        // TODO: handle this better, instead of one exception per product, collect them all and return them all
        $cart->lineItems()->each(function ($lineItem) use ($request, $cart) {
            $this->validateStock($request, $cart, $lineItem);
        });

        $cart = $this->handleCustomerInformation($request, $cart);
        $cart = $this->redeemCoupon($request, $cart);

        if (! $cart->customer()) {
            throw ValidationException::withMessages([
                'customer' => __('Order cannot be created without customer information.'),
            ]);
        }

        if (! $cart->taxableAddress()) {
            throw ValidationException::withMessages([
                'shipping_line_1' => __('Order cannot be created without an address.'),
            ]);
        }

        $cart->merge($validated);
        $cart->save();

        $order = Order::makeFromCart($cart);
        $order->save();

        if ($order->coupon()) {
            event(new CouponRedeemed($order->coupon(), $order));
        }

        Cart::forgetCurrentCart();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart);
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }
}
