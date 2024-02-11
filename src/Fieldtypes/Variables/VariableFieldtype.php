<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes\Variables;

use Statamic\Fieldtypes\Hidden;

abstract class VariableFieldtype extends Hidden
{
    protected $component = 'hidden';

    protected $categories = ['special'];

    protected $selectable = false;

    protected function resource()
    {
        return $this->field()->parent();
    }
}
