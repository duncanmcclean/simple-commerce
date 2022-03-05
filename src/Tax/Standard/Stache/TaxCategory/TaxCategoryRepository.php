<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxCategory;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxCategory;
use Statamic\Data\DataCollection;
use Statamic\Stache\Stache;

class TaxCategoryRepository
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('simple-commerce-tax-categories');
    }

    public function all(): DataCollection
    {
        if ($this->query()->count() === 0) {
            $this->makeDefaultCategory();
        }

        return $this->query()->get();
    }

    public function find($id): ?TaxCategory
    {
        return $this->query()->where('id', $id)->first();
    }

    public function save($taxCategory)
    {
        $this->store->save($taxCategory);
    }

    public function delete($taxCategory)
    {
        $this->store->delete($taxCategory);
    }

    public function query()
    {
        return new TaxCategoryQueryBuilder($this->store);
    }

    public function make(): TaxCategory
    {
        return new TaxCategory();
    }

    protected function makeDefaultCategory()
    {
        $this->make()
            ->id('default')
            ->name('Default')
            ->description(__('Will be used for all products where a category has not been assigned.'))
            ->save();

        TaxZone::make()
            ->id('everywhere')
            ->name('Everywhere')
            ->save();

        TaxRate::make()
            ->id('default-rate')
            ->name('Default')
            ->category('default')
            ->zone('everywhere')
            ->rate(0)
            ->save();
    }
}
