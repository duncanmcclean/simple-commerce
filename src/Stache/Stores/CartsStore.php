<?php

namespace DuncanMcClean\SimpleCommerce\Stache\Stores;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;

class CartsStore extends BasicStore
{
    protected $storeIndexes = [
        'customer',
        'updated_at',
    ];

    public function key()
    {
        return 'carts';
    }

    public function getItemKey($item)
    {
        return $item->id();
    }

    public function makeItemFromFile($path, $contents): CartContract
    {
        $data = YAML::file($path)->parse($contents);

        return Cart::make()
            ->id(Arr::pull($data, 'id'))
            ->customer(Arr::pull($data, 'customer'))
            ->coupon(Arr::pull($data, 'coupon'))
            ->lineItems(Arr::pull($data, 'line_items'))
            ->grandTotal(Arr::pull($data, 'grand_total'))
            ->subTotal(Arr::pull($data, 'sub_total'))
            ->discountTotal(Arr::pull($data, 'discount_total'))
            ->taxTotal(Arr::pull($data, 'tax_total'))
            ->shippingTotal(Arr::pull($data, 'shipping_total'))
            ->data($data);
    }
}
