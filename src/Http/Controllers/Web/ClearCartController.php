<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Helpers\Cart;

class ClearCartController extends Controller
{
    public function __construct()
    {
        $this->cart = new Cart();
    }

    public function __invoke()
    {
        $this->cart->clear();

        return redirect()
            ->back()
            ->with('message', 'Cart has been cleared.');
    }
}
