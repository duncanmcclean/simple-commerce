<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxZone;

use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxZone;
use Statamic\Data\DataCollection;
use Statamic\Stache\Stache;

class TaxZoneRepository
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('simple-commerce-tax-zones');
    }

    public function all(): DataCollection
    {
        return $this->query()->get();
    }

    public function find($id): ?TaxZone
    {
        return $this->query()->where('id', $id)->first();
    }

    public function save($form)
    {
        $this->store->save($form);
    }

    public function delete($entry)
    {
        $this->store->delete($entry);
    }

    public function query()
    {
        return new TaxZoneQueryBuilder($this->store);
    }

    public function make(): TaxZone
    {
        return new TaxZone();
    }
}
