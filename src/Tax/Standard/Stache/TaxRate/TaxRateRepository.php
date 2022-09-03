<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxRate;

use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxRate;
use Statamic\Data\DataCollection;
use Statamic\Stache\Stache;

class TaxRateRepository
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

    public function save($taxRate)
    {
        $this->store->save($taxRate);
    }

    public function delete($taxRate)
    {
        $this->store->delete($taxRate);
    }

    public function query()
    {
        return new TaxRateQueryBuilder($this->store);
    }

    public function make(): TaxRate
    {
        return new TaxRate();
    }
}
