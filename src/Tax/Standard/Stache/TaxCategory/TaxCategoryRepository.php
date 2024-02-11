<?php

namespace DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxCategory;

use DuncanMcClean\SimpleCommerce\Contracts\TaxCategoryRepository as Contract;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxCategory;
use Statamic\Data\DataCollection;
use Statamic\Stache\Stache;

class TaxCategoryRepository implements Contract
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

    public function save($taxCategory): void
    {
        $this->store->save($taxCategory);
    }

    public function delete($taxCategory): void
    {
        $this->store->delete($taxCategory);
    }

    public function query(): TaxCategoryQueryBuilder
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
            ->name(__('Default'))
            ->description(__('Will be used for all products where a category has not been assigned.'))
            ->save();

        $this->make()
            ->id('shipping')
            ->name(__('Shipping'))
            ->description(__('This tax category will be automatically applied to shipping costs.'))
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

        TaxRate::make()
            ->id('default-shipping-rate')
            ->name('Default - Shipping')
            ->category('shipping')
            ->zone('everywhere')
            ->rate(0)
            ->save();
    }
}
