<?php

namespace DuncanMcClean\SimpleCommerce\Stache\Stores;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use Illuminate\Support\Str;
use Statamic\Entries\GetDateFromPath;
use Statamic\Entries\GetSlugFromPath;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;

class OrdersStore extends BasicStore
{
    protected $storeIndexes = [
        'site', 'order_number', 'date', 'cart', 'customer',
    ];

    public function key()
    {
        return 'orders';
    }

    public function getItemKey($item)
    {
        return $item->id();
    }

    public function makeItemFromFile($path, $contents): OrderContract
    {
        $site = $this->extractSiteFromPath($path);
        $data = YAML::file($path)->parse($contents);

        return Order::make()
            ->site($site)
            ->id(Arr::pull($data, 'id'))
            ->orderNumber((new GetSlugFromPath)($path))
            ->date((new GetDateFromPath)($path))
            ->cart(Arr::pull($data, 'cart'))
            ->status(Arr::pull($data, 'status'))
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
