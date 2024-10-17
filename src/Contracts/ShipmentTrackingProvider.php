<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Shipping\Tracking\TrackingNumberStatus;
use Illuminate\Support\Collection;

/**
 * Likely a wrapper around an API class, this class is responsible for querying
 * The shipping provider and updating tracking information.
 */
interface ShipmentTrackingProvider {
  /**
   * The human readable name of the provider
   * e.g. FedEx, DHL, etc.
   */
  public function name(): string;

  /**
   * The machine readable name of the provider that will be stored in config
   * files, entries, and models.

   * e.g. fedex, dhl, etc.
   */
  public function slug(): string;

  /**
   * A glide compatible image containing the logo of the company.
   * TODO: We should define recommended size(s) for this.
   */
  public function logo(): Asset;

  /**
   * (OPTIONAL)
   * A list of countries to which this provider will ship packages.
   */
  public function countries(): Collection;

  /**
   * Given a Tracking number, converts the number's status string into an enum.
   *
   * @throws \InvalidArgumentException if the $number's status is unrecognizable.
   */
  public function mapStatus(string $status): TrackingNumberStatus;
}
