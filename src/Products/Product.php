<?php

namespace DoubleThreeDigital\SimpleCommerce\Products;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product as Contract;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory as TaxCategoryFacade;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\IsEntry;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxCategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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
        if ($this->purchasableType() === 'variants' || !$this->has('stock')) {
            return null;
        }

        return (int) $this->get('stock');
    }

    public function purchasableType(): string
    {
        if (isset($this->data()['product_variants']['variants'])) {
            return 'variants';
        }

        return 'product';
    }

    public function variants(): Collection
    {
        if (!isset($this->data()['product_variants']['options'])) {
            return collect();
        }

        return collect($this->get('product_variants')['options'])
            ->map(function ($variantOption) {
                $productVariant = (new ProductVariant())
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
        return $this->variants()->filter(function ($variant) use ($optionKey) {
            return $variant->key() === $optionKey;
        })->first();
    }

    public function collection(): string
    {
        return SimpleCommerce::productDriver()['collection'];
    }

    public function taxCategory(): ?TaxCategory
    {
        if (!isset($this->data['tax_category'])) {
            return TaxCategoryFacade::find('default');
        }

        return TaxCategoryFacade::find($this->data['tax_category']);
    }

    public static function bindings(): array
    {
        return [];
    }
}
