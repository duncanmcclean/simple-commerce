<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\AddToCartRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\RemoveFromCartRequest;
use DoubleThreeDigital\SimpleCommerce\Http\UsesCart;
use Illuminate\Http\Request;
use Statamic\View\View;

class CartController extends Controller
{
    use UsesCart;

    public function index()
    {
        return (new View())
            ->template('simple-commerce::web.cart')
            ->layout('simple-commerce::web.layout')
            ->with([
                'title' => 'Cart',
            ]);
    }

    public function store(AddToCartRequest $request)
    {
        $this->createCart();

        $this->cart()->add($this->cartId, [
            'product' => $request->product,
            'variant' => $request->variant,
            'quantity' => (int) $request->quantity,
        ]);

        return back()->with('success', 'Success! Product added to your cart.');
    }

    public function destroy(RemoveFromCartRequest $request)
    {
        $this->createCart();

        $this->cart()->remove($this->cartId, $request->item_id);

        return back()->with('success', 'Success! Product removed from your cart.');
    }
}
