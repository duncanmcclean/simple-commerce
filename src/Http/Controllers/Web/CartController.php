<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Events\AddedToCart;
use Damcclean\Commerce\Facades\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $request)
    {
        // WIP probs need some sort of csrf checking here
        // WIP and some validation

        $slug = $request->slug;
        $quantity = $request->quantity;

        $items = $request->session()->get('cart');

        collect($items)
            ->where('slug', $slug)
            ->each(function ($item, $quantity) {
                $item['quantity'] = $quantity;
            });

        $items[] = [
            'slug' => $slug,
            'quantity' => $quantity,
        ];

        $request->session()->put('cart', $items);

        event(new AddedToCart(Product::findBySlug($slug)));

        return redirect()
            ->back()
            ->with('message', 'Added product to cart.');
    }

    public function destroy(Request $request)
    {
        $cart = collect($request->session()->get('cart'))
            ->reject(function ($product) use ($request) {
                if ($product['slug'] == $request->slug) {
                    return true;
                }

                return false;
            });

        $request->session()->put('cart', $cart);

        return redirect()
            ->back()
            ->with('message', 'Removed product from cart.');
    }
}
