<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

class StatusLogEvent implements Arrayable
{
    public function __construct(public string|OrderStatus|PaymentStatus $status, public int $timestamp, public array $data = [])
    {
        if (is_string($status)) {
            $this->status = $this->getStatusFromValue($status);
        }
    }

    public function date(): Carbon
    {
        return Carbon::parse($this->timestamp);
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'timestamp' => $this->timestamp,
            'data' => $this->data,
        ];
    }

    // TODO: maybe refactor this, it's not pretty
    private function getStatusFromValue(string $value): OrderStatus|PaymentStatus
    {
        try {
            return OrderStatus::from($value);
        } catch (\Throwable $th) {
            return PaymentStatus::from($value);
        }
    }
}
