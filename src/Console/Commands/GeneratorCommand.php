<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use Statamic\Console\Commands\GeneratorCommand as StatamicGeneratorCommand;

class GeneratorCommand extends StatamicGeneratorCommand
{
    /**
     * We need to do this ourselves so it uses the
     * Simple Commerce stub path.
     *
     * @return string
     */
    protected function getStub($stub = null)
    {
        $stub = $stub ?? $this->stub;

        return __DIR__.'/stubs/'.$stub;
    }
}
