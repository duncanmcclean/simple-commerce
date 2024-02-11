<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeShippingMethod extends GeneratorCommand
{
    use RunsInPlease;

    protected $name = 'statamic:make:shipping-method';

    protected $description = 'Create a new shipping method';

    protected $type = 'ShippingMethod';

    protected $stub = 'shipping.php.stub';

    public function handle()
    {
        if (parent::handle() === false) {
            return false;
        }
    }
}
