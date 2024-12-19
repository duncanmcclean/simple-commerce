<?php

namespace DuncanMcClean\SimpleCommerce\Taxes;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass as TaxClassContract;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxZone as TaxZoneContract;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Illuminate\Support\Collection;

class GetTaxRates
{
    public function __invoke(Cart $cart, TaxClassContract $taxClass): Collection
    {
        return TaxZone::all()
            ->filter(function (TaxZoneContract $taxZone) use ($cart) {
                if ($taxZone->get('type') === 'countries') {
                    return in_array($cart->taxableAddress()->country, $taxZone->get('countries'));
                }

                if ($taxZone->get('type') === 'states') {
                    return in_array($cart->taxableAddress()->country, $taxZone->get('countries'))
                        && in_array($cart->taxableAddress()->state, $taxZone->get('states'));
                }

                if ($taxZone->get('type') === 'postcodes') {
                    $matchesPostcode = collect($taxZone->get('postcodes'))
                        ->filter(fn ($postcode) => fnmatch($postcode, $cart->taxableAddress()->postcode))
                        ->isNotEmpty();

                    return in_array($cart->taxableAddress()->country, $taxZone->get('countries')) && $matchesPostcode;
                }
            })
            ->map(fn (TaxZoneContract $taxZone) => $taxZone->rates()->get($taxClass->handle()))
            ->filter();
    }
}