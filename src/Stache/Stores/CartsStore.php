<?php

namespace DuncanMcClean\SimpleCommerce\Stache\Stores;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;

class CartsStore extends BasicStore
{
    protected $storeIndexes = [
        'site', 'customer', 'updated_at',
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
        $site = $this->extractSiteFromPath($path);
        $data = YAML::file($path)->parse($contents);

        return Cart::make()
            ->site($site)
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

    protected function extractSiteFromPath($path)
    {
        $site = Site::default()->handle();

        if (Site::multiEnabled()) {
            $site = pathinfo($path, PATHINFO_DIRNAME);
            $site = Str::after($site, $this->directory());

            return $site;
        }

        return $site;
    }
}
