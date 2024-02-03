<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Facades\Statamic\UpdateScripts\Manager as UpdateScriptManager;

class RunUpdateScripts extends Command
{
    use RunsInPlease;

    protected $name = 'sc:run-update-scripts';

    protected $description = 'Runs the update scripts for Simple Commerce v6.0.0.';

    public function handle()
    {
        $this->info("Running update scripts...");

        UpdateScriptManager::runUpdatesForSpecificPackageVersion(
            package: 'doublethreedigital/simple-commerce',
            oldVersion: '5.0.0',
            console: $this
        );
    }
}
