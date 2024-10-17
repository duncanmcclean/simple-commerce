<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use \Carbon\Carbon;
use DuncanMcClean\SimpleCommerce\Shipping\Tracking\TrackingNumberStatus;

interface TrackingNumber {
  /**
   * The 'id' of the tracking number, used to query the status.
   */
  public function trackingNumber(): string;

  /**
   * The service that is used to update the tracking number information.
   * The 'slug' of the shipper should be stored, and then retrieved using the 
   * `ShipmentTracking` Facade. 
   *
   * @throws if Provider is not insta
   */
  public function shippingProvider(): ShipmentTrackingProvider;

  /**
   * The datetime when the tracking nummber was created. Could be called
   * "shippedAt", but that could be misleading as tracking numbers are created
   * before the package is actually shipped.
   */
  public function createdAt(): Carbon;

  /**
   * The current status of the package in generic terms.
   * This should be saved in the shipping provider's native format,
   * and then converted into a common format by passing saved status to the
   * shipping provider's `mapStatus` method.
   */
  public function status(): TrackingNumberStatus;

  /**
   * (OPTIONAL)
   * The datetime when the status was most recently updated. Useful if you want
   * to check for jobs on a cron, or be notified if info goes stale for too long.
   */
  public function statusUpdatedAt(): Carbon;
}

