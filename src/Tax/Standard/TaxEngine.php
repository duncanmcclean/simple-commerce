<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\TaxEngine as Contract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\PreventCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxRate as StandardTaxRate;
use DoubleThreeDigital\SimpleCommerce\Tax\TaxCalculation;
use Illuminate\Support\Facades\Config;

class TaxEngine implements Contract
{
    public function name(): string
    {
        return 'Standard';
    }

    public function calculate(Order $order, LineItem $lineItem): TaxCalculation
    {
        $taxRate = $this->decideOnRate($order, $lineItem);

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

    protected function decideOnRate(Order $order, LineItem $lineItem): ?StandardTaxRate
    {
        $product = $lineItem->product();

        /** @var \DoubleThreeDigital\SimpleCommerce\Orders\Address */
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
