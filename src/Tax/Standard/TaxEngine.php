<?php

namespace DuncanMcClean\SimpleCommerce\Tax\Standard;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Contracts\TaxEngine as Contract;
use DuncanMcClean\SimpleCommerce\Exceptions\PreventCheckout;
use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Orders\Address;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxRate as StandardTaxRate;
use DuncanMcClean\SimpleCommerce\Tax\TaxCalculation;
use Illuminate\Support\Facades\Config;

class TaxEngine implements Contract
{
    public function name(): string
    {
        return 'Standard';
    }

    public function calculateForLineItem(Order $order, LineItem $lineItem): TaxCalculation
    {
        $taxRate = $this->decideOnLineItemRate($order, $lineItem);

        if (! $taxRate) {
            $noRateAvailable = config('simple-commerce.tax_engine_config.behaviour.no_rate_available');

            if ($noRateAvailable === 'default_rate') {
                $taxRate = TaxRate::find('default-rate');
            }

            if ($noRateAvailable === 'prevent_checkout') {
                throw new PreventCheckout(__('This order cannot be completed as no tax rate is available.'));
            }
        }

        $taxAmount = ($lineItem->total() / 100) * ($taxRate->rate() / (100 + $taxRate->rate()));
        $itemTax = (int) round($taxAmount * 100);

        return new TaxCalculation($itemTax, $taxRate->rate(), $taxRate->includeInPrice());
    }

    protected function decideOnLineItemRate(Order $order, LineItem $lineItem): ?StandardTaxRate
    {
        $product = $lineItem->product();

        /** @var \DuncanMcClean\SimpleCommerce\Orders\Address */
        $address = config('simple-commerce.tax_engine_config.address') === 'billing'
            ? $order->billingAddress()
            : $order->shippingAddress();

        if (! $address) {
            $noAddressProvided = config('simple-commerce.tax_engine_config.behaviour.no_address_provided');

            if ($noAddressProvided === 'default_address') {
                $address = $this->defaultAddress();
            }

            if ($noAddressProvided === 'prevent_checkout') {
                throw new PreventCheckout(__('This order cannot be completed as no address has been added to this order.'));
            }
        }

        $taxRateQuery = TaxRate::all()
            ->filter(function ($taxRate) use ($product) {
                return $taxRate->category()->id() === $product->taxCategory()->id();
            });

        $taxZoneQuery = TaxZone::all()->where('id', '!=', 'everywhere');

        if ($address->country()) {
            $taxZoneQuery = $taxZoneQuery->filter(function ($taxZone) use ($address) {
                return $taxZone->country() === $address->country();
            });

            if ($address->region()) {
                $taxZoneQuery = $taxZoneQuery->filter(function ($taxZone) use ($address) {
                    if (! $taxZone->region()) {
                        return true;
                    }

                    return $taxZone->region() === $address->region();
                });
            }
        }

        if ($taxZoneQuery->count() < 1) {
            $taxZoneQuery = TaxZone::query()->where('id', 'everywhere')->get();
        }

        return $taxRateQuery
            ->filter(function ($taxRate) use ($taxZoneQuery) {
                return $taxRate->zone()->id() === $taxZoneQuery->first()->id();
            })
            ->first();
    }

    public function calculateForShipping(Order $order, ShippingMethod $shippingMethod): TaxCalculation
    {
        $taxRate = $this->decideOnShippingRate($order, $shippingMethod);

        if (! $taxRate) {
            $noRateAvailable = config('simple-commerce.tax_engine_config.behaviour.no_rate_available');

            if ($noRateAvailable === 'default_rate') {
                $taxRate = TaxRate::find('default-rate');
            }

            if ($noRateAvailable === 'prevent_checkout') {
                throw new PreventCheckout(__('This order cannot be completed as no tax rate is available.'));
            }
        }

        $taxAmount = ($order->shippingTotal() / 100) * ($taxRate->rate() / (100 + $taxRate->rate()));
        $itemTax = (int) round($taxAmount * 100);

        return new TaxCalculation($itemTax, $taxRate->rate(), $taxRate->includeInPrice());
    }

    protected function decideOnShippingRate(Order $order, ShippingMethod $shippingMethod): ?StandardTaxRate
    {
        /** @var \DuncanMcClean\SimpleCommerce\Orders\Address */
        $address = config('simple-commerce.tax_engine_config.address') === 'billing'
            ? $order->billingAddress()
            : $order->shippingAddress();

        if (! $address) {
            $noAddressProvided = config('simple-commerce.tax_engine_config.behaviour.no_address_provided');

            if ($noAddressProvided === 'default_address') {
                $address = $this->defaultAddress();
            }

            if ($noAddressProvided === 'prevent_checkout') {
                throw new PreventCheckout(__('This order cannot be completed as no address has been added to this order.'));
            }
        }

        $taxRateQuery = TaxRate::all()
            ->filter(function ($taxRate) {
                return $taxRate->category()->id() === TaxCategory::find('shipping')->id();
            });

        $taxZoneQuery = TaxZone::all()->where('id', '!=', 'everywhere');

        if ($address->country()) {
            $taxZoneQuery = $taxZoneQuery->filter(function ($taxZone) use ($address) {
                return $taxZone->country() === $address->country();
            });

            if ($address->region()) {
                $taxZoneQuery = $taxZoneQuery->filter(function ($taxZone) use ($address) {
                    if (! $taxZone->region()) {
                        return true;
                    }

                    return $taxZone->region() === $address->region();
                });
            }
        }

        if ($taxZoneQuery->count() < 1) {
            $taxZoneQuery = TaxZone::query()->where('id', 'everywhere')->get();
        }

        return $taxRateQuery
            ->filter(function ($taxRate) use ($taxZoneQuery) {
                return $taxRate->zone()->id() === $taxZoneQuery->first()->id();
            })
            ->first();
    }

    protected function defaultAddress(): Address
    {
        $defaultAddressConfig = Config::get('simple-commerce.tax_engine_config.default_address');

        $addressData = collect([
            'default_name' => '',
            'default_address_line1' => $defaultAddressConfig['address_line_1'],
            isset($defaultAddressConfig['address_line_2']) ? $defaultAddressConfig['address_line_2'] : '',
            'default_city' => $defaultAddressConfig['city'],
            'default_country' => $defaultAddressConfig['country'],
            'default_zip_code' => $defaultAddressConfig['zip_code'],
            'default_region' => $defaultAddressConfig['region'],
        ]);

        return Address::from('default', $addressData);
    }
}
