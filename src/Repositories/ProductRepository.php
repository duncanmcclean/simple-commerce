<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\ProductRepository as ContractsProductRepository;
use DoubleThreeDigital\SimpleCommerce\Exceptions\ProductNotFound;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;

class ProductRepository implements ContractsProductRepository
{
    use DataRepository;

    public function save(): self
    {
        Entry::make()
            ->collection(config('simple-commerce.collections.products'))
            ->locale($this->site)
            ->published(false)
            ->slug($this->slug)
            ->id($this->id)
            ->data(array_merge($this->data, [
                'title' => $this->title,
            ]))
            ->save();

        return $this;
    }

    public function entry(): EntriesEntry
    {
        $entry = Entry::find($this->id);

        if (!$entry) {
            throw new ProductNotFound(__('simple-commerce::products.product_not_found', ['id' => $this->id]));
        }

        return $entry;
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'slug'  => $this->slug,
            'title' => $this->title,
            'price' => $this->data['price'],
            'stock' => $this->stockCount(),
        ];
    }

    public function stockCount()
    {
        if (! isset($this->stock)) {
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
        if (! isset($this->data['product_variants']['options'])) {
            return null;
        }

        return collect($this->data['product_variants']['options'])
            ->where('key', $optionKey)
            ->first();
    }

    public function isExemptFromTax()
    {
        return $this->has('exempt_from_tax') && $this->get('exempt_from_tax') === true;
    }
}
