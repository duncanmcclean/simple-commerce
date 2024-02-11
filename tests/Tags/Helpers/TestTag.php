<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Tags\Helpers;

use DuncanMcClean\SimpleCommerce\Tags\SubTag;

class TestTag extends SubTag
{
    public function index()
    {
        return 'This is the index method.';
    }

    public function cheese()
    {
        return 'This is the cheese method.';
    }

    public function wildcard()
    {
        return 'This is the wildcard method.';
    }
}
