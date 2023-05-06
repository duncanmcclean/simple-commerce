<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags\Helpers;

use DoubleThreeDigital\SimpleCommerce\Tags\SimpleCommerceTag as Tag;

class SimpleCommerceTag extends Tag
{
    protected $tagClasses = [
        'test' => TestTag::class,
    ];
}
