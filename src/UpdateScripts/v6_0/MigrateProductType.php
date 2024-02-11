<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v6_0;

use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Products\ProductVariant;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\File;
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
                $blueprint->removeField('is_digital_product');
                $blueprint->removeField('downloadable_asset');
                $blueprint->removeField('download_limit');
                $blueprint->saveQuietly();
            });
        }
    }
}
