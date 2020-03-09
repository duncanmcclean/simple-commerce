<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\UsesCart;
use Illuminate\Http\Request;

class ClearCartController extends Controller
{
    use UsesCart;

    public function __invoke(Request $request)
    {
        $this->replaceCart();

        return back()->with('success', 'Success! Your cart has been cleared.');
    }
}
