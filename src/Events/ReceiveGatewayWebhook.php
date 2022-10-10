<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ReceiveGatewayWebhook
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public array $payload)
    {
    }
}
