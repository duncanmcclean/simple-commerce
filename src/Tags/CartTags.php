<?php

namespace Damcclean\Commerce\Tags;

use Damcclean\Commerce\Facades\Product;
use Statamic\Tags\Tags;

class CartTags extends Tags
{
    protected static $handle = 'cart';

    public function index()
    {
        $items = request()->session()->get('cart');

        return collect($items)
            ->map(function ($item) {
                $product = (array) Product::findBySlug($item['slug']);
                $product['quantity'] = $item['quantity'];
                $product['price'] = $product['price'] * $product['quantity'];

                return collect($product);
            });
    }

    public function count()
    {
        return $this->index()->count();
    }

    public function total()
    {
        $items = $this->index();
        $total = 0;

        foreach ($items as $item) {
            $total = $item['price'];
        }

        return $total;
    }
}
