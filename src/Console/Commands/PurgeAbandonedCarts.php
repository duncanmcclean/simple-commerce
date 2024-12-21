<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Jobs\PurgeAbandonedCarts as PurgeAbandonedCartsJob;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class PurgeAbandonedCarts extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:simple-commerce:purge-abandoned-carts';

    protected $description = 'Purges abandoned carts.';

    public function handle()
    {
        $this->components->info('Purging abandoned carts...');

        PurgeAbandonedCartsJob::dispatch();
    }
}
