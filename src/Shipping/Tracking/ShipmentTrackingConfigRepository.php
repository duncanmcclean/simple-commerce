<?php

namespace DuncanMcClean\SimpleCommerce\Shipping\Tracking;

use DuncanMcClean\SimpleCommerce\Contracts\ShipmentTrackingProvider;
use DuncanMcClean\SimpleCommerce\Contracts\ShipmentTrackingRepository;
use DuncanMcClean\SimpleCommerce\Exceptions\ShipmentTrackingProviderNotFound;
use Statamic\Data\DataCollection;

class ShipmentTrackingConfigRepository implements ShipmentTrackingRepository {
    protected DataCollection $providers;

    public function __construct(array $providers) {
        $this->providers = new DataCollection();

        foreach ($providers as $slug => $providerClass) {
            $provider = app($providerClass);
            $realSlug = $slug ?: $provider->slug();

            $this->providers->put($realSlug, $providerClass);
        }
    }

    public function all(): DataCollection {
        return $this->providers;
    }

    /**
     * @throws DuncanMcClean\SimpleCommerce\Exceptions\ShipmentTrackingProviderNotFound;
     */
    public function find(string $slug): ShipmentTrackingProvider {
        if ($provider = $this->providers->get($slug)) {
            return $provider;
        } else {
            throw new ShipmentTrackingProviderNotFound($slug);
        }
    }  
}
