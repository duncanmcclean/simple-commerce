<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Contracts\Products\Product;
use DuncanMcClean\SimpleCommerce\Contracts\Products\ProductRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Exceptions\ProductNotFound;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;

class ProductRepository implements RepositoryContract
{
    protected array $collections = [];

    public function __construct()
    {
        $this->collections = config('statamic.simple-commerce.products.collections', ['products']);
    }

    public function all()
    {
        return Entry::query()
            ->whereIn('collection', $this->collections)
            ->get()
            ->map(fn ($entry) => $this->fromEntry($entry));
    }

    public function find($id): ?Product
    {
        $entry = Entry::query()
            ->whereIn('collection', $this->collections)
            ->where('id', $id)
            ->find($id);

        if (! $entry) {
            return null;
        }

        return $this->fromEntry($entry);
    }

    public function findOrFail($id): Product
    {
        $product = $this->find($id);

        if (! $product) {
            throw new ProductNotFound("Product [{$id}] could not be found.");
        }

        return $product;
    }

    public function fromEntry(EntryContract $entry): Product
    {
        $product = app(Product::class)
            ->id($entry->id())
            ->collection($entry->collection())
            ->blueprint($entry->blueprint())
            ->data($entry->data())
            ->locale($entry->locale())
            ->template($entry->template())
            ->layout($entry->layout());

        if ($entry->hasDate()) {
            $product->date($entry->date());
        }

        return $product;
    }

    public static function bindings(): array
    {
        return [
            Product::class => \DuncanMcClean\SimpleCommerce\Products\Product::class,
        ];
    }
}
