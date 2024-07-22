<?php

namespace DuncanMcClean\SimpleCommerce\Stache\Stores;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;

class OrdersStore extends BasicStore
{
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
        $orderNumber = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        return Order::make()
            ->id($id = Arr::pull($data, 'id'))
            ->orderNumber($id !== $orderNumber ? $orderNumber : null)
            ->customer(Arr::pull($data, 'customer'))
            ->lineItems(Arr::pull($data, 'line_items'))
            ->grandTotal(Arr::pull($data, 'grand_total'))
            ->subTotal(Arr::pull($data, 'sub_total'))
            ->discountTotal(Arr::pull($data, 'discount_total'))
            ->taxTotal(Arr::pull($data, 'tax_total'))
            ->shippingTotal(Arr::pull($data, 'shipping_total'))
            ->paymentGateway(Arr::pull($data, 'payment_gateway'))
            ->paymentData(Arr::pull($data, 'payment_info'))
            ->shippingMethod(Arr::pull($data, 'shipping_method'))
            ->data($data);
    }
}
