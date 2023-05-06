<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags\Helpers;

use Statamic\Tags\Tags;

class SimpleCommerceTag extends Tags
{
    protected $tagClasses = [
        'test' => TestTag::class,
    ];
}
