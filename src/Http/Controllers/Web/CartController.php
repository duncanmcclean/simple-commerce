<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\AddToCartRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\RemoveFromCartRequest;
use Illuminate\Http\Request;
use Statamic\View\View;

class CartController extends Controller
{
    public $cartId;

    public function index()
    {
        return (new View())
            ->template('commerce::web.cart')
            ->layout('commerce::web.layout')
            ->with([
                'title' => 'Cart',
            ]);
    }

    public function store(AddToCartRequest $request)
    {
        $this->createCart($request);

        Cart::add($this->cartId, [
            'product' => $request->product,
            'variant' => $request->variant,
            'quantity' => (int) $request->quantity,
        ]);

        return back()
            ->with('success', 'Added item to cart.');
    }

    public function destroy(RemoveFromCartRequest $request)
    {
        $this->createCart($request);

        Cart::remove($this->cartId, $request->item_id);

        return back()
            ->with('success', 'Removed product from cart.');
    }

    protected function createCart(Request $request)
    {
        if (! $request->session()->get('commerce_cart_id')) {
            $request->session()->put('commerce_cart_id', Cart::create());
            $request->session()->save();
        }

        $this->cartId = $request->session()->get('commerce_cart_id');
    }
}
