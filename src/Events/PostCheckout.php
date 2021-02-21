<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class PostCheckout
{
    use Dispatchable;
    use InteractsWithSockets;

    // TODO: maybe we should also provide this event with the actual order too, not just the data?

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
