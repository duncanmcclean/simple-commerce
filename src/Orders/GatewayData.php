<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Gateway;
use DuncanMcClean\SimpleCommerce\Gateways\Manager;
use Illuminate\Support\Collection;

class GatewayData
{
    public $gateway;

    public $data;

    public $refund;

    public function __construct(array $gatewayData)
    {
        if (isset($gatewayData['use'])) {
            $this->gateway = $gatewayData['use'];
        }

        if (isset($gatewayData['data'])) {
            $this->data = collect($gatewayData['data']);
        }

        if (isset($gatewayData['refund'])) {
            $this->refund = collect($gatewayData['refund']);
        }
    }

    public function gateway(): Manager
    {
        if (! $this->gateway) {
            return null;
        }

        return Gateway::use($this->gateway);
    }

    public function data(): Collection
    {
        return $this->data;
    }

    public function refund(): ?Collection
    {
        return $this->refund;
    }

    public function toArray(): array
    {
        return [
            'use' => $this->gateway,
            'data' => $this->data ? $this->data->toArray() : null,
            'refund' => $this->refund ? $this->refund->toArray() : null,
        ];
    }
}
