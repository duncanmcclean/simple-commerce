<?php

namespace DuncanMcClean\SimpleCommerce\Shipping\Tracking;

use Carbon\Carbon;
use DuncanMcClean\SimpleCommerce\Contracts\ShipmentTrackingProvider;
use DuncanMcClean\SimpleCommerce\Contracts\TrackingNumber as BaseTrackingNumber;
use DuncanMcClean\SimpleCommerce\Facades;

class TrackingNumber Implements BaseTrackingNumber {
    public function __construct(
        protected string $trackingNumber,
        protected string $shippingProvider,
        protected Carbon $createdAt,
        protected string $status,
        protected Carbon $statusUpdatedAt,
    ) {}

    public function trackingNumber(): string {
        return $this->trackingNumber;
    }

    public function shippingProvider(): ShipmentTrackingProvider {
        return Facades\ShipmentTracking::find($this->shippingProvider);
    }

    public function createdAt(): Carbon {
        return $this->createdAt;
    }

    public function status(): TrackingNumberStatus {
        return $this
            ->shippingProvider()
            ->mapStatus($this->status);
    }

    public function statusUpdatedAt(): Carbon {
        return $this->statusUpdatedAt;
    }
}

