<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Helpers\Cart;
use Illuminate\Http\Request;

class ClearCartController extends Controller
{
    public $cartId;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    public function __invoke(Request $request)
    {
        $this->createCart($request);

        $this->cart->clear($this->cartId);

        $request->session()->remove('commerce_cart_id');
        $this->createCart($request);

        return back()
            ->with('success', 'Your cart has been cleared.');
    }

    protected function createCart(Request $request)
    {
        if (! $request->session()->get('commerce_cart_id')) {
            $request->session()->put('commerce_cart_id', $this->cart->create());
            $request->session()->save();
        }

        $this->cartId = $request->session()->get('commerce_cart_id');
    }
}
