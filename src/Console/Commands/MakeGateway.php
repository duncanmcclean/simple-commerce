<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeGateway extends GeneratorCommand
{
    use RunsInPlease;

    protected $name = 'statamic:make:gateway';
    protected $description = 'Create a new gateway';
    protected $type = 'Gateway';
    protected $stub = 'gateway.php.stub';

    public function handle()
    {
        if (parent::handle() === false) {
            return false;
        }
    }
}
