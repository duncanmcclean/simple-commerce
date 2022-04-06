<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v3_0;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;
use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class ConfigureWhitelistedFields extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.0.0-beta.1');
    }

    public function update()
    {
        if (! isset(SimpleCommerce::orderDriver()['collection'])) {
            return;
        }

        $ordersFieldWhitelist = Collection::findByHandle(SimpleCommerce::orderDriver()['collection'])
            ->entryBlueprint()
            ->fields()
            ->items()
            ->pluck('handle')
            ->reject(function ($fieldHandle) {
                return in_array($fieldHandle, [
                    'title', 'is_paid', 'customer', 'coupon', 'items', 'shipping_section', 'billing_section', 'slug',
                    'paid_date', 'items_total', 'coupon_total', 'tax_total', 'shipping_total', 'grand_total', 'order_number',
                ]);
            })
            ->toArray();

        ConfigWriter::edit('simple-commerce')
            ->set('field_whitelist', [
                'orders' => $ordersFieldWhitelist,
                'line_items' => [],
            ])
            ->save();
    }
}
