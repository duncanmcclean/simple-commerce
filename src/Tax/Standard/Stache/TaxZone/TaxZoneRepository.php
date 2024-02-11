<?php

namespace DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxZone;

use DuncanMcClean\SimpleCommerce\Contracts\TaxZoneRepository as Contract;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxZone;
use Statamic\Data\DataCollection;
use Statamic\Stache\Stache;

class TaxZoneRepository implements Contract
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

    public function save($taxZone): void
    {
        $this->store->save($taxZone);
    }

    public function delete($taxZone): void
    {
        $this->store->delete($taxZone);
    }

    public function query(): TaxZoneQueryBuilder
    {
        return new TaxZoneQueryBuilder($this->store);
    }

    public function make(): TaxZone
    {
        return new TaxZone();
    }
}
