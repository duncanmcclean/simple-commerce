<?php

namespace Damcclean\Commerce\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Money extends Fieldtype
{
    public function preProcess($data)
    {
        return $data;
    }

    public function process($data)
    {
        return $data;
    }
}
