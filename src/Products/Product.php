<?php

namespace DoubleThreeDigital\SimpleCommerce\Products;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product as Contract;
use DoubleThreeDigital\SimpleCommerce\Facades\Product as ProductFacade;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory as TaxCategoryFacade;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxCategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Http\Resources\API\EntryResource;

class Product implements Contract
{
    use HasData;

    public $id;
    public $data;

    public $related;

    public function __construct()
    {
        $this->data = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function related($related = null)
    {
        return $this
            ->fluentlyGetOrSet('related')
            ->args(func_get_args());
    }

    public function stockCount()
    {
        if ($this->purchasableType() === ProductType::VARIANT() || ! $this->has('stock')) {
            return null;
        }

        return (int) $this->get('stock');
    }

    public function purchasableType(): ProductType
    {
        if (isset($this->get('product_variants')['variants'])) {
            return ProductType::VARIANT();
        }

        return ProductType::PRODUCT();
    }

    public function variants(): Collection
    {
        if (! isset($this->get('product_variants')['options'])) {
            return collect();
        }

        return collect($this->get('product_variants')['options'])
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
        return $this->variants()->filter(function ($variant) use ($optionKey) {
            return $variant->key() === $optionKey;
        })->first();
    }

    public function taxCategory(): ?TaxCategory
    {
        if (! $this->get('tax_category')) {
            return TaxCategoryFacade::find('default');
        }

        return TaxCategoryFacade::find($this->get('tax_category'));
    }

    public function beforeSaved()
    {
        return null;
    }

    public function afterSaved()
    {
        return null;
    }

    public function save(): self
    {
        if (method_exists($this, 'beforeSaved')) {
            $this->beforeSaved();
        }

        ProductFacade::save($this);

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete(): void
    {
        ProductFacade::delete($this);
    }

    public function fresh(): self
    {
        $freshProduct = ProductFacade::find($this->id());

        $this->id = $freshProduct->id;
        $this->data = $freshProduct->data;
        $this->related = $freshProduct->related;

        return $this;
    }

    public function toResource()
    {
        return ['data' => []]; // TODO

        return new EntryResource($this->related());
    }

    public function toAugmentedArray(): array
    {
        $blueprintFields = $this->related()->blueprint()->fields()->items()->reject(function ($field) {
            return $field['handle'] === 'value';
        })->pluck('handle')->toArray();

        $augmentedData = $this->related()->toAugmentedArray($blueprintFields);

        return array_merge(
            $this->toArray(),
            $augmentedData,
        );
    }
}
