<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use Statamic\Data\DataCollection;

interface ShipmentTrackingRepository
{
    public function all(): DataCollection;
    public function find(string $slug): ShipmentTrackingProvider;
}
