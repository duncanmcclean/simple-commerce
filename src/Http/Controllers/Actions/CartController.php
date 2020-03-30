<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartDestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartUpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Http\UsesCart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;

class CartController extends Controller
{
    use UsesCart;

    public function store(CartStoreRequest $request)
    {
        $this->createCart();

        $this->cart()->add($this->cartId, [
            'product'   => $request->product,
            'variant'   => $request->variant,
            'quantity'  => (int) $request->quantity,
        ]);

        return $request->redirect ? redirect($request->redirect) : back();
    }

    public function update(CartUpdateRequest $request)
    {
        CartItem::where('uuid', $request->item->id)
            ->first()
            ->update([
                'quantity' => $request->quantity,
            ]);

        return $request->redirect ? redirect($request->redirect) : back();
    }

    public function destroy(CartDestroyRequest $request)
    {
        if ($request->clear != null) {
            $this->replaceCart();

            return $request->redirect ? redirect($request->redirect) : back();
        }

        $this->createCart();

        $this->cart()->remove($this->cartId, $request->item_id);

        return $request->redirect ? redirect($request->redirect) : back();
    }
}
