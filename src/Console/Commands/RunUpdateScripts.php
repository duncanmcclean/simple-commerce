<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class RunUpdateScripts extends Command
{
    use RunsInPlease;

    protected $name = 'sc:run-update-scripts';

    protected $description = 'Runs the update scripts for Simple Commerce v6.0.0.';

    public function handle()
    {
        $this->info('Running update scripts...');
        $this->info('This could take a while if you have lots of orders.');

        // For some reason, the "proper" way of doing this didn't work so we're
        // doing it manually here.
        app('statamic.update-scripts')
            ->filter(function (array $script) {
                return $script['package'] === 'duncanmcclean/simple-commerce';
            })
            ->each(function (array $script) {
                $updateScript = new $script['class']($script['package'], $this);

                $updateScript->update();
            });
    }
}
