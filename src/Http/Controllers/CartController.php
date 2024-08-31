<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\UpdateCartRequest;
use DuncanMcClean\SimpleCommerce\Http\Resources\API\CartResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Exceptions\NotFoundHttpException;

class CartController
{
    use Concerns\HandlesCustomerInformation;

    public function index(Request $request)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        return new CartResource(Cart::current());
    }

    public function update(UpdateCartRequest $request)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        $cart = Cart::current();
        $validated = $request->validated();

        if ($validated['coupon'] ?? false) {
            // TODO: Add the coupon to the cart.
        }

        $cart = $this->handleCustomerInformation($request, $cart);
        $cart->merge(Arr::except($validated, ['coupon', 'customer']));

        $cart->recalculate()->save();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart->fresh());
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    public function destroy(Request $request)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        Cart::forgetCurrentCart();

        if ($request->ajax() || $request->wantsJson()) {
            return [];
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }
}
