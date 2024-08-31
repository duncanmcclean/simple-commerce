<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\AddLineItemRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\UpdateLineItemRequest;
use DuncanMcClean\SimpleCommerce\Http\Resources\API\CartResource;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;

class CartLineItemsController
{
    use Concerns\HandlesCustomerInformation, Concerns\ValidatesStock, Concerns\HandlePrerequisiteProducts;

    public function store(AddLineItemRequest $request)
    {
        $cart = Cart::current();
        $product = Product::find($request->product);

        $data = $request->collect()->except([
            '_redirect', '_error_redirect', 'product', 'variant', 'quantity', 'first_name', 'last_name'. 'email', 'customer',
        ]);

        $this->validateStock($request, $cart);

        $cart = $this->handleCustomerInformation($request, $cart);
        $cart = $this->handlePrerequisiteProducts($request, $cart, $product);

        $productIsAlreadyInCart = $cart->lineItems()
            ->where('product', $product->id())
            ->when($request->get('variant'), function ($collection) use ($request) {
                return $collection->where('variant', $request->get('variant'));
            })
            ->when(config('simple-commerce.cart.unique_metadata', false), function ($collection) use ($data) {
                return $collection->filter(function (LineItem $lineItem) use ($data) {
                    foreach ($data as $key => $value) {
                        if ($lineItem->get($key) !== $value) {
                            return false;
                        }
                    }
                });
            });

        if ($productIsAlreadyInCart->count() > 0) {
            $lineItem = $productIsAlreadyInCart->first();

            $cart->lineItems()->update(
                id: $lineItem->id(),
                data: $lineItem->data()->merge($data)->merge([
                    'quantity' => (int) $lineItem->quantity() + ($request->quantity ?? 1),
                ])->all()
            );
        } else {
            $cart->lineItems()->create($data->merge([
                'product' => $request->product,
                'variant' => $request->variant,
                'quantity' => $request->quantity ?? 1,
            ])->all());
        }

        $cart->save();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart->fresh());
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    public function update(UpdateLineItemRequest $request, string $lineItem)
    {
        $cart = Cart::current();
        $lineItem = $cart->lineItems()->find($lineItem);

        throw_if(! $lineItem, NotFoundHttpException::class);

        $data = $request->collect()->except([
            '_redirect', '_error_redirect', 'product', 'variant', 'quantity', 'first_name', 'last_name'. 'email', 'customer',
        ]);

        $this->validateStock($request, $cart, $lineItem);

        $cart->lineItems()->update(
            id: $lineItem->id(),
            data: $lineItem->data()->merge($data)->merge([
                'variant' => $request->variant ?? $lineItem->variant,
                'quantity' =>  $request->quantity ?? $lineItem->quantity(),
            ])->all()
        );

        $cart->save();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart->fresh());
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    public function destroy(Request $request, string $lineItem)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        $cart = Cart::current();
        $lineItem = $cart->lineItems()->find($lineItem);

        throw_if(! $lineItem, NotFoundHttpException::class);

        $cart->lineItems()->remove($lineItem->id());
        $cart->save();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart->fresh());
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }
}
