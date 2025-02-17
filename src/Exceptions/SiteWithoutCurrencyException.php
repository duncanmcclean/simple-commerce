<?php

namespace DuncanMcClean\SimpleCommerce\Exceptions;

use Exception;
use Statamic\Sites\Site;

class SiteWithoutCurrencyException extends Exception
{
    public function __construct(Site $site)
    {
        parent::__construct("The currency attribute is missing from the [{$site->handle()}] site.");
    }
}
