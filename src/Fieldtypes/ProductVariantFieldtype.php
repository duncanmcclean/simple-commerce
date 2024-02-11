<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use Statamic\Fields\Fieldtype;

class ProductVariantFieldtype extends Fieldtype
{
    public function component(): string
    {
        return 'product-variant';
    }

    public function preload()
    {
        return [
            'api' => cp_route('simple-commerce.fieldtype-api.product-variant'),
        ];
    }

    public function preProcess($data)
    {
        if (is_string($data)) {
            return [
                'product' => null,
                'variant' => $data,
            ];
        }

        return $data;
    }

    public function augment($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            throw new \Exception('Variant field is using old format. Please re-save the order in the CP to save as new format.');
        }

        $product = Product::find($value['product']);

        if ($product->purchasableType() === ProductType::Product) {
            return null;
        }

        $augmentedValue = $product
            ->resource()
            ->augmentedValue('product_variants');

        if (! is_array($augmentedValue)) {
            $augmentedValue = $augmentedValue->value();
        }

        $variantSearch = collect($augmentedValue['options'])
            ->where('key', $value['variant']);

        if ($variantSearch->count() === 0) {
            return null;
        }

        return $variantSearch->first();
    }
}
