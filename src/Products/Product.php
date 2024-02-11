<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Contracts\Product as Contract;
use DuncanMcClean\SimpleCommerce\Data\HasData;
use DuncanMcClean\SimpleCommerce\Facades\Product as ProductFacade;
use DuncanMcClean\SimpleCommerce\Facades\TaxCategory as TaxCategoryFacade;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxCategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Http\Resources\API\EntryResource;
use Statamic\Sites\Site;

class Product implements Contract
{
    use HasData;

    public $id;

    public $price;

    public $productVariants;

    public $stock;

    public $taxCategory;

    public $data;

    public $resource;

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

    public function price($price = null)
    {
        return $this
            ->fluentlyGetOrSet('price')
            ->args(func_get_args());
    }

    public function productVariants($productVariants = null)
    {
        return $this
            ->fluentlyGetOrSet('productVariants')
            ->args(func_get_args());
    }

    public function stock($stock = null)
    {
        return $this
            ->fluentlyGetOrSet('stock')
            ->getter(function ($value) {
                if ($this->purchasableType() === ProductType::Variant) {
                    return null;
                }

                return $value;
            })
            ->setter(function ($value) {
                if ($value === null) {
                    return null;
                }

                return (int) $value;
            })
            ->args(func_get_args());
    }

    public function taxCategory($taxCategory = null)
    {
        return $this
            ->fluentlyGetOrSet('taxCategory')
            ->getter(function ($value) {
                if (! $value) {
                    return TaxCategoryFacade::find('default');
                }

                return $value;
            })
            ->setter(function ($taxCategory) {
                if ($taxCategory instanceof TaxCategory) {
                    return $taxCategory;
                }

                return TaxCategoryFacade::find($taxCategory);
            })
            ->args(func_get_args());
    }

    public function resource($resource = null)
    {
        return $this
            ->fluentlyGetOrSet('resource')
            ->args(func_get_args());
    }

    public function site(): ?Site
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $this->resource()->site();
        }

        return null;
    }

    public function purchasableType(): ProductType
    {
        if ($this->productVariants) {
            return ProductType::Variant;
        }

        return ProductType::Product;
    }

    public function variantOptions(): Collection
    {
        if (! $this->productVariants) {
            return collect();
        }

        return collect($this->productVariants()['options'])
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
        $this->price = $freshProduct->price;
        $this->productVariants = $freshProduct->productVariants;
        $this->stock = $freshProduct->stock;
        $this->taxCategory = $freshProduct->taxCategory;
        $this->data = $freshProduct->data;
        $this->resource = $freshProduct->resource;

        return $this;
    }

    public function toResource()
    {
        return new EntryResource($this->resource());
    }

    public function toAugmentedArray($keys = null): array
    {
        return $this->resource()->toAugmentedArray($keys);
    }

    public function toAugmentedCollection($keys = null): Collection
    {
        return $this->resource()->toAugmentedCollection($keys);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
