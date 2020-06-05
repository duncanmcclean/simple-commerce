<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class VariantOutOfStock
{
    use Dispatchable, InteractsWithSockets;

    public $variant;

    /**
     * VariantOutOfStock constructor.
     * @param Variant $variant
     */
    public function __construct(Variant $variant)
    {
        $this->variant = $variant;
    }
}
