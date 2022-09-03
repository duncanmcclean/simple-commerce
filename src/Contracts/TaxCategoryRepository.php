<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxCategory\TaxCategoryQueryBuilder;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxCategory;
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
