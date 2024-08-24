<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\DestroyRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\IndexRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\UpdateCartRequest;
use DuncanMcClean\SimpleCommerce\Http\Resources\API\CartResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Exceptions\NotFoundHttpException;

class CartController extends BaseActionController
{
    use HandlesCustomerInformation;

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

        $cart->save();

        return new CartResource($cart->fresh());
    }

    public function destroy(DestroyRequest $request)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        Cart::forgetCurrentCart();

        return [];
    }
}
