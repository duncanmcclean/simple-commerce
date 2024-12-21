<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Contracts\Products\Product as Contract;
use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass as TaxClassContract;
use DuncanMcClean\SimpleCommerce\Facades\TaxClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry;

class Product extends Entry implements Contract, Purchasable
{
    public function type(): ProductType
    {
        if ($this->value('product_variants')) {
            return ProductType::Variant;
        }

        return ProductType::Product;
    }

    public function price(): int
    {
        if ($this->type() === ProductType::Variant) {
            throw new \Exception('The Product::price() method can not be called on a variant product.');
        }

        $price = $this->value('price');

        if (str_contains($price, '.')) {
            $price = number_format($price, 2, '.', '');
            $price = (int) str_replace('.', '', (string) $price);
        }

        return $price ?? 0;
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
                return (new ProductVariant)
                    ->key($variantOption['key'])
                    ->product($this)
                    ->name($variantOption['variant'])
                    ->price($variantOption['price'])
                    ->when(isset($variantOption['stock']), function ($productVariant) use ($variantOption) {
                        $productVariant->stock($variantOption['stock']);
                    })
                    ->data(Arr::except($variantOption, ['key', 'variant', 'price', 'stock']));
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

    public function purchasablePrice(): int
    {
        return $this->price();
    }

    public function purchasableTaxClass(): ?TaxClassContract
    {
        return TaxClass::find($this->value('tax_class'));
    }
}
