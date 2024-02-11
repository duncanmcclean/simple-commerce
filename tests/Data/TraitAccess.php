<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Data;

use DuncanMcClean\SimpleCommerce\Data\HasData;

// We're using this `TraitAccess` class instead of simply 'using' the class
// as some of the trait's method name's conflict with those of Testbench's Test Case.
class TraitAccess
{
    public $data;

    use HasData;

    public function __construct()
    {
        $this->data = collect();
    }
}
