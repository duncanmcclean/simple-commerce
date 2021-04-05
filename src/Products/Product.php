<?php

namespace DoubleThreeDigital\SimpleCommerce\Products;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product as Contract;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\IsEntry;

class Product implements Contract
{
    use IsEntry;
    use HasData;

    public $id;
    public $site;
    public $title;
    public $slug;
    public $data;
    public $published;

    protected $entry;
    protected $collection;

    public function stockCount()
    {
        if (!isset($this->stock)) {
            return null;
        }

        return (int) $this->data['stock'];
    }

    public function purchasableType(): string
    {
        if (isset($this->data['product_variants']['variants'])) {
            return 'variants';
        }

        return 'product';
    }

    public function variantOption(string $optionKey): ?array
    {
        if (!isset($this->data['product_variants']['options'])) {
            return null;
        }

        return collect($this->data['product_variants']['options'])
            ->where('key', $optionKey)
            ->first();
    }

    public function isExemptFromTax(): bool
    {
        return $this->has('exempt_from_tax')
            && $this->get('exempt_from_tax') === true;
    }

    public function collection(): string
    {
        return config('simple-commerce.collections.products');
    }

    public static function bindings(): array
    {
        return [];
    }
}
