<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxZone\TaxZoneQueryBuilder;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxZone;
use Statamic\Data\DataCollection;

interface TaxZoneRepository
{
    public function all(): DataCollection;

    public function find($id): ?TaxZone;

    public function save($taxZone): void;

    public function delete($taxZone): void;

    public function query(): TaxZoneQueryBuilder;

    public function make(): TaxZone;
}
