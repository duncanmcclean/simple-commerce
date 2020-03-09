<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AttributeUpdated
{
    use Dispatchable, InteractsWithSockets;

    public $attribute;

    public function __construct(Attribute $attribute)
    {
        $this->attribute = $attribute;
    }
}
