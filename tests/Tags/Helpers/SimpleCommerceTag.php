<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Tags\Helpers;

use DuncanMcClean\SimpleCommerce\Tags\SimpleCommerceTag as Tag;

class SimpleCommerceTag extends Tag
{
    protected $tagClasses = [
        'test' => TestTag::class,
    ];
}
