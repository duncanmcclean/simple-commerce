<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Input\InputArgument;

class MakeGateway extends GeneratorCommand
{
    use RunsInPlease;

    protected $name = 'statamic:make:gateway';

    protected $description = 'Create a new gateway';

    protected $type = 'Gateway';

    protected $stub = 'gateway-onsite.php.stub';

    public function handle()
    {
        if ($this->argument('type') === 'onsite') {
            $this->stub = 'gateway-onsite.php.stub';
        }

        if ($this->argument('type') === 'offsite') {
            $this->stub = 'gateway-offsite.php.stub';
        }

        if (parent::handle() === false) {
            return false;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
            ['addon', InputArgument::OPTIONAL, 'The package name of an addon (ie. john/my-addon)'],
            ['type', InputArgument::OPTIONAL, 'The type of gateway you wish to generate.', 'onsite'],
        ];
    }
}
