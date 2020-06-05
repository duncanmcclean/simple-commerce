<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Statamic\Http\Controllers\CP\CpController;

class DuplicateProductController extends CpController
{
    public function __invoke(Product $product)
    {
        $this->authorize('update', $product);

        $duplicate = $product->replicate();

        $duplicate->title .= ' (Duplicate)';
        $duplicate->slug .= '-duplicate';
        $duplicate->save();

        collect($product->variants)
            ->each(function (Variant $variant) use ($duplicate) {
                $duplicateVariant = $variant->replicate();

                $duplicateVariant->product_id = $duplicate->id;
                $duplicateVariant->sku .= '-duplicate';
                $duplicateVariant->save();
            });

        return back()->with('success', 'Product has been duplicated.');
    }
}
