<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

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

        return redirect()
            ->back()
            ->with('message', 'Added product to Cart');
    }
}
