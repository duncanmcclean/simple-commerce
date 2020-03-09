<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class VariantLowStock
{
    use Dispatchable, InteractsWithSockets;

    public $variant;

    public function __construct(Variant $variant)
    {
        $this->variant = $variant;
    }
}
