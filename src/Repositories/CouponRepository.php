<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository as ContractsCouponRepository;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry as EntryInstance;
use Statamic\Facades\Entry;

class CouponRepository implements ContractsCouponRepository
{
    use DataRepository;

    public function all(): Collection
    {
        return Entry::whereCollection(config('simple-commerce.collections.coupons'));
    }

    public function findByCode(string $code): self
    {
        $entry = Entry::query()
            ->where('collection', config('simple-commerce.collections.coupons'))
            ->where('slug', $code)
            ->first();

        if (!$entry) {
            throw new CouponNotFound(__('simple-commerce.coupons.coupon_not_found', ['code' => $code]));
        }

        return $this->find($entry->id());
    }

    public function save(): self
    {
        Entry::make()
            ->collection(config('simple-commerce.collections.coupons'))
            ->locale($this->site)
            ->published(false)
            ->slug($this->slug)
            ->id($this->id)
            ->data(array_merge($this->data, [
                'title' => $this->title,
            ]))
            ->save();

        return $this;
    }

    public function entry(): EntryInstance
    {
        $entry = Entry::find($this->id);

        if (!$entry) {
            throw new CouponNotFound(__('simple-commerce.coupons.coupon_not_found', ['code' => $this->slug]));
        }

        return $entry;
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'code'  => $this->slug,
            'type'  => isset($this->data['type']) ? $this->data['type'] : null,
            'value' => isset($this->data['value']) ? $this->data['value'] : null,
        ];
    }

    public function isValid(EntryInstance $order): bool
    {
        if ($this->get('minimum_cart_value') && $order->data()->get('items_total') != null) {
            if ($order->data()->get('items_total') < $this->data['minimum_cart_value']) {
                return false;
            }
        }

        if ($this->get('redeemed') && $this->get('maximum_uses')) {
            if ($this->data['redeemed'] >= $this->data['maximum_uses']) {
                return false;
            }
        }

        return true;
    }

    public function redeem(): self
    {
        $this->set(
            'redeemed',
            isset($this->data['redeemed']) ? $this->data['redeemed'] + 1 : 1
        );

        return $this;
    }
}
