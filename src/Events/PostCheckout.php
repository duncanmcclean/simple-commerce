<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class PostCheckout
{
    use Dispatchable, InteractsWithSockets;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
