<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Events\AddedToCart;
use Damcclean\Commerce\Facades\Product;
use Damcclean\Commerce\Helpers\Cart;
use Damcclean\Commerce\Http\Requests\CartDeleteRequest;
use Damcclean\Commerce\Http\Requests\CartStoreRequest;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct()
    {
        $this->cart = new Cart();
    }

    public function store(CartStoreRequest $request)
    {
        $validate = $request->validated();

        $items = $this->cart->all();

        $items[] = [
            'slug' => $request->slug,
            'quantity' => $request->quantity,
        ];

        $this->cart->replace($items);

        event(new AddedToCart(Product::findBySlug($request->slug)));

        return redirect()
            ->back()
            ->with('message', 'Added product to cart.');
    }

    public function destroy(CartDeleteRequest $request)
    {
        $validate = $request->validated();

        $this->cart->remove($request->slug);

        return redirect()
            ->back()
            ->with('message', 'Removed product from cart.');
    }
}
