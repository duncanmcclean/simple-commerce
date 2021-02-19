<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class PreCheckout
{
    use Dispatchable, InteractsWithSockets;

    // TODO: maybe we should also provide this event with the actual order too, not just the data?

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
