<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ReceiveGatewayWebhook
{
    use Dispatchable;
    use InteractsWithSockets;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
