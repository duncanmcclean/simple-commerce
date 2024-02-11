<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxRate\TaxRateQueryBuilder;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxRate;
use Statamic\Data\DataCollection;

interface TaxRateRepository
{
    public function all(): DataCollection;

    public function find($id): ?TaxRate;

    public function save($taxRate): void;

    public function delete($taxRate): void;

    public function query(): TaxRateQueryBuilder;

    public function make(): TaxRate;
}
