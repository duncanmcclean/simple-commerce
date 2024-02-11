<?php

namespace DuncanMcClean\SimpleCommerce\UpdateScripts\v6_0;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Products\ProductVariant;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\UpdateScripts\UpdateScript;

class MigrateProductType extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        Product::query()->chunk(100, function ($products) {
            $products->each(function ($product) {
                $productType = 'physical';

                if ($product->get('is_digital_product')) {
                    $productType = 'digital';
                }

                if ($variantOptions = $product->variantOptions()) {
                    $hasDigitalProducts = $variantOptions->filter(function (ProductVariant $productVariant) {
                        return $productVariant->get('is_digital_product') === true;
                    })->count();

                    if ($hasDigitalProducts > 0) {
                        $productType = 'digital';
                    }
                }

                $entry = $product->resource();
                $entry->set('product_type', $productType);
                $entry->saveQuietly();
            });
        });

        if ($productCollection = SimpleCommerce::productDriver()['collection']) {
            $productCollection = Collection::find($productCollection);

            $productCollection->entryBlueprints()->each(function (Blueprint $blueprint) {
                if ($blueprint->hasTab(__('Digital Product'))) {
                    if ($blueprint->hasField('is_digital_product')) {
                        $blueprint->removeField('is_digital_product', __('Digital Product'));
                    }

                    if ($blueprint->hasField('downloadable_asset')) {
                        $blueprint->removeField('downloadable_asset', __('Digital Product'));
                    }

                    if ($blueprint->hasField('download_limit')) {
                        $blueprint->removeField('download_limit', __('Digital Product'));
                    }
                }

                if ($blueprint->hasField('product_variants')) {
                    $productVariantsField = $blueprint->field('product_variants');

                    $productVariantsField->setConfig([
                        'option_fields' => collect($productVariantsField->config()['option_fields'] ?? [])
                            ->filter(function ($value, $key) {
                                return $value['handle'] !== 'is_digital_product'
                                    && $value['handle'] !== 'downloadable_asset'
                                    && $value['handle'] !== 'download_limit';
                            })
                            ->toArray(),
                    ]);
                }

                $blueprint->saveQuietly();
            });
        }
    }
}
