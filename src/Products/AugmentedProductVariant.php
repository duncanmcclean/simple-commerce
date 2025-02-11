<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use Statamic\Data\AbstractAugmented;

class AugmentedProductVariant extends AbstractAugmented
{
    private $cachedKeys;

    public function keys()
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = collect()
            ->merge($this->commonKeys())
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys(): array
    {
        return ['key', 'product', 'name', 'price', 'stock'];
    }
}
