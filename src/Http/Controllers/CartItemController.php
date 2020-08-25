<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\UpdateRequest;
use DoubleThreeDigital\SimpleCommerce\SessionCart;
use Illuminate\Support\Arr;
use Statamic\Facades\Stache;

class CartItemController extends BaseActionController
{
    use SessionCart;

    public function store(StoreRequest $request)
    {
        if ($this->hasSessionCart()) {
            $cart = $this->getSessionCart();
        } else {
            $cart = $this->makeSessionCart();
        }

        $items = $cart->data['items'];

        $cart->update([
            'items' => array_merge($items, [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $request->product,
                    'quantity' => (int) $request->quantity,
                    'total'    => 0000,
                ]
            ]),
        ])->calculateTotals();

        return $this->withSuccess($request);
    }

    public function update(UpdateRequest $request, string $requestItem)
    {
        $cart = $this->getSessionCart();

        $cart->update([
            'items' => collect($cart->data['items'] ?? [])
                ->map(function ($item) use ($request, $requestItem) {
                    if ($item['id'] !== $requestItem) {
                        return $item;
                    }

                    return array_merge(
                        $item,
                        Arr::except($request->all(), ['_token', '_params', '_redirect']),
                    );
                })
                ->toArray(),
        ])->calculateTotals();

        return $this->withSuccess($request);
    }

    public function destroy(DestroyRequest $request, string $item)
    {
        $cart = $this->getSessionCart();

        $cart->update([
            'items' => collect($cart->data['items'])
                ->reject(function ($item) {
                    return $item['id'] != $item;
                })
                ->toArray(),
        ])->calculateTotals();

        return $this->withSuccess($request);
    }
}
