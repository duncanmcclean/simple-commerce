<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v5_0;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\UpdateScripts\UpdateScript;

class CreateShippingTaxCategory extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.1.0');
    }

    public function update()
    {
        if (! SimpleCommerce::isUsingStandardTaxEngine()) {
            return;
        }

        TaxCategory::make()
            ->id('shipping')
            ->name(__('Shipping'))
            ->description(__('This tax category will be automatically applied to shipping costs.'))
            ->save();

        TaxRate::make()
            ->id('default-shipping-rate')
            ->name('Default Shipping')
            ->category('shipping')
            ->zone('everywhere')
            ->rate(0)
            ->save();
    }
}
