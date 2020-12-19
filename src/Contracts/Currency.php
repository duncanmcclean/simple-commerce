<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Statamic\Sites\Site;

interface Currency
{
    public function get(Site $site): array;

    public function parse($price, Site $site): string;
}
