<?php

namespace DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxRate;

use DuncanMcClean\SimpleCommerce\Contracts\TaxRateRepository as Contract;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxRate;
use Statamic\Data\DataCollection;
use Statamic\Stache\Stache;

class TaxRateRepository implements Contract
{
    protected $stache;

    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('simple-commerce-tax-rates');
    }

    public function all(): DataCollection
    {
        return $this->query()->get();
    }

    public function find($id): ?TaxRate
    {
        return $this->query()->where('id', $id)->first();
    }

    public function save($taxRate): void
    {
        $this->store->save($taxRate);
    }

    public function delete($taxRate): void
    {
        $this->store->delete($taxRate);
    }

    public function query(): TaxRateQueryBuilder
    {
        return new TaxRateQueryBuilder($this->store);
    }

    public function make(): TaxRate
    {
        return new TaxRate();
    }
}
