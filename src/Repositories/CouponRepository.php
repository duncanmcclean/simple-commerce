<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository as ContractsCouponRepository;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry as EntryInstance;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class CouponRepository implements ContractsCouponRepository
{
    public string $id;
    public string $title;
    public string $code;
    public array $data;

    public function make(): self
    {
        $this->id = Stache::generateId();

        return $this;
    }

    public function all(): Collection
    {
        return Entry::whereCollection(config('simple-commerce.collections.coupons'));
    }

    public function find(string $id): self
    {
        $this->id = $id;

        $entry = Entry::find($this->id);

        $this->title = $entry->title;
        $this->slug = $entry->slug();
        $this->data = $entry->data()->toArray();

        return $this;
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

    public function data(array $data = []): self
    {
        if ($data === []) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function save(): self
    {
        Entry::make()
            ->collection(config('simple-commerce.collections.coupons'))
            ->published(false)
            ->slug($this->slug)
            ->id($this->id)
            ->data(array_merge($this->data, [
                'title' => $this->title,
            ]))
            ->save();

        return $this;
    }

    public function update(array $data, bool $mergeData = true): self
    {
        if ($mergeData) {
            $data = array_merge($data, $this->data);
        }

        $this->entry()
            ->data($data)
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
}
