<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry;
use DuncanMcClean\SimpleCommerce\Contracts\Products\Product as Contract;

class Product extends Entry implements Contract
{
    public function type(): ProductType
    {
        if ($this->value('product_variants')) {
            return ProductType::Variant;
        }

        return ProductType::Product;
    }

    public function price(): ?int
    {
        if ($this->type() === ProductType::Variant) {
            return null;
        }

        return $this->value('price', 0);
    }

    public function productVariants(): array
    {
        return $this->value('product_variants', []);
    }

    public function stock(): ?int
    {
        if ($this->type() === ProductType::Variant) {
            return null;
        }

        return $this->value('stock');
    }

    public function variantOptions(): Collection
    {
        if (! $this->value('product_variants')) {
            return collect();
        }

        return collect(Arr::get($this->value('product_variants'), 'options'))
            ->map(function ($variantOption) {
                $productVariant = (new ProductVariant)
                    ->key($variantOption['key'])
                    ->product($this)
                    ->name($variantOption['variant'])
                    ->price($variantOption['price'])
                    ->data(Arr::except($variantOption, ['key', 'variant', 'price', 'stock']));

                if (isset($variantOption['stock'])) {
                    $productVariant->stock($variantOption['stock']);
                }

                return $productVariant;
            });
    }

    public function variant(string $optionKey): ?ProductVariant
    {
        return $this->variantOptions()->filter(function ($variant) use ($optionKey) {
            return $optionKey === $variant->key();
        })->first();
    }

    public function fresh(): self
    {
        return \DuncanMcClean\SimpleCommerce\Facades\Product::find($this->id);
    }
}
