<?php

namespace DuncanMcClean\SimpleCommerce\Coupons;

use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Orders\Order;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Illuminate\Support\Collection;
use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Site;

class AugmentedCoupon extends AbstractAugmented
{
    private $cachedKeys;

    public function keys(): array
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = $this->data->data()->keys()
            ->merge($this->data->supplements()->keys())
            ->merge($this->commonKeys())
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return [
            'id',
            'code',
            'type',
            'amount',
            'discount_text',
        ];
    }
}
