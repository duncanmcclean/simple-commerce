<?php

namespace Damcclean\Commerce\Helpers;

use Damcclean\Commerce\Facades\Product;
use Illuminate\Support\Collection;

class Cart
{
    public function all()
    {
        return collect(request()->session()->get('cart'))
            ->map(function ($item) {
                $product = Product::findBySlug($item['slug'])->toArray();

                $product['quantity'] = $item['quantity'];
                $product['price'] = $product['price'] * $product['quantity'];

                return collect($product);
            });
    }

//    public function get()
//    {
//
//    }
//
//    public function put()
//    {
//
//    }

    public function replace($items)
    {
        return request()->session()->put('cart', $items);
    }

    public function remove($slug)
    {
        $items = collect($this->cart->all())
            ->reject(function ($product) use ($slug) {
                if ($product['slug'] == $slug) {
                    return true;
                }

                return false;
            });

        return $this->replace($items);
    }

    public function total()
    {
        $total = 0;

        $this->all()
            ->each(function ($item) use(&$total) {
                $total += $item['price'];
            });

        return $total * 100;
    }

    public function count()
    {
        return $this->all()->count();
    }

    public function clear()
    {
        return request()->session()->forget('cart');
    }
}
