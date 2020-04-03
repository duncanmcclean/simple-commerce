<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;

class VersionCommand extends Command
{
    protected $signature = 'simple-commerce:version';
    protected $description = 'Get the currently installed version of Simple Commerce';

    public function handle()
    {
        $version = SimpleCommerce::getVersion();
        $this->info("You are running Simple Commerce {$version}.");
    }
}
