<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository as ContractsCouponRepository;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry as EntryInstance;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class CouponRepository implements ContractsCouponRepository
{
    public string $id;
    public string $code;
    public array $data;

    public function make(): self
    {
        $this->id = Stache::generateId();

        return $this;
    }

    public function all(): Collection
    {
        return Entry::whereCollection('coupons');
    }

    public function find(string $id): self
    {
        $entry = Entry::find($id);

        $this->id = $entry->id();
        $this->code = $entry->slug();
        $this->data = $entry->data()->toArray();

        return $this;
    }

    public function update(array $data, bool $mergeData = true): self
    {
        if ($mergeData) {
            $data = array_merge($this->data, $data);
        }

        Entry::find($this->id)
            ->data($data)
            ->save();

        return $this;    
    }

    public function entry(): EntryInstance
    {
        return Entry::find($this->id);
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