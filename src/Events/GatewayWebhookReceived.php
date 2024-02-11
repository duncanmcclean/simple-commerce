<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class GatewayWebhookReceived
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public array $payload)
    {
    }
}
