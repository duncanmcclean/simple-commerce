<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxZone;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class TaxZoneSaved implements ProvidesCommitMessage
{
    public function __construct(public TaxZone $taxZone) {}

    public function commitMessage()
    {
        return __('Tax Zone saved', [], config('statamic.git.locale'));
    }
}
