<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as Contract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\IsEntry;
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
        $entry = Entry::findBySlug($code, config('simple-commerce.collections.coupons'));

        if (! $entry) {
            throw new CouponNotFound(__('simple-commerce.coupons.coupon_not_found'));
        }

        return $this->find($entry->id());
    }

    // TODO: refactor
    public function isValid(EntryInstance $order): bool
    {
        if ($this->has('minimum_cart_value') && $order->has('items_total')) {
            if ($order->data()->get('items_total') < $this->get('minimum_cart_value')) {
                return false;
            }
        }

        if ($this->has('redeemed') && $this->has('maximum_uses')) {
            if ($this->has('redeemed') >= $this->get('maximum_uses')) {
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
