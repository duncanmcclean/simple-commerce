<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class InfoCommand extends Command
{
    use RunsInPlease;

    protected $name = 'statamic:simple-commerce:info';
    protected $description = 'Get information from Simple Commerce, like booted gateways, etc.';

    public function handle()
    {
        $this->info('Booted Gateways');

        foreach (SimpleCommerce::gateways() as $gateway) {
            $this->line("{$gateway['name']}");
        }
    }
}
