<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as Contract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\IsEntry;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry as EntryInstance;
use Statamic\Facades\Entry;

class Coupon implements Contract
{
    use IsEntry, HasData;

    public $id;
    public $site;
    public $title;
    public $slug;
    public $data;
    public $published;

    protected $entry;
    protected $collection;

    public function findByCode(string $code): self
    {
        $entry = $this->query()->where('slug', $code)->first();

        return $this->find($entry->id());
    }

    // TODO: refactor
    public function isValid(EntryInstance $order): bool
    {
        if ($this->data['minimum_cart_value'] != null && $order->data()->get('items_total') != null) {
            if ($order->data()->get('items_total') < $this->data['minimum_cart_value']) {
                return false;
            }
        }

        if ($this->data['redeemed'] != null && $this->data['maximum_uses']) {
            if ($this->data['redeemed'] >= $this->data['maximum_uses']) {
                return false;
            }
        }

        return true;
    }

    public function redeem(): self
    {
        $this->set('redeemed', $this->has('redeemed') ? $this->get('redeemed') + 1 : 1);

        return $this;
    }

    public function collection(): string
    {
        return config('simple-commerce.collections.coupons');
    }

    public static function bindings(): array
    {
        return [];
    }
}
