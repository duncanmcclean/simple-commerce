<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository as ContractsCouponRepository;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as FacadesEntry;
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
        return FacadesEntry::whereCollection('coupons');
    }

    public function find(string $id): self
    {
        $entry = FacadesEntry::find($id);

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

        FacadesEntry::find($this->id)
            ->data($data)
            ->save();

        return $this;    
    }

    public function isValid(Entry $order): bool
    {
        // If type is 'percentage'
            // Get value

        // If type is 'fixed'
            // Get value

        // check if coupon has been used max times
        // check if cart value is more than minimum cart value

        return true;
    }
}