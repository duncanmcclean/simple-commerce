<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes\Variables;

use Statamic\Fieldtypes\Hidden;

abstract class VariableFieldtype extends Hidden
{
    protected $component = 'hidden';
    protected $categories = ['special'];

    protected function entry()
    {
        return $this->field()->parent();
    }
}
