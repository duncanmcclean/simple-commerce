<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxCategory\TaxCategoryQueryBuilder;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxCategory;
use Statamic\Data\DataCollection;

interface TaxCategoryRepository
{
    public function all(): DataCollection;

    public function find($id): ?TaxCategory;

    public function save($taxCategory): void;

    public function delete($taxCategory): void;

    public function query(): TaxCategoryQueryBuilder;

    public function make(): TaxCategory;
}
