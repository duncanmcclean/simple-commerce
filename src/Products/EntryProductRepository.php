<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Contracts\Product;
use DuncanMcClean\SimpleCommerce\Contracts\ProductRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Exceptions\ProductNotFound;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class EntryProductRepository implements RepositoryContract
{
    protected $collection;

    public function __construct()
    {
        $this->collection = SimpleCommerce::productDriver()['collection'];
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(EntryQueryBuilder::class, [
            'store' => app('stache')->store('entries'),
        ])->where('collection', $this->collection);
    }

    public function find($id): ?Product
    {
        $entry = Entry::find($id);

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

    public function fromEntry($entry)
    {
        $product = app(Product::class)
            ->resource($entry)
            ->id($entry->id());

        if ($entry->has('price') || $entry->originValues()->has('price')) {
            $product->price($entry->value('price'));
        }

        if ($entry->has('product_variants') || $entry->originValues()->has('product_variants')) {
            $product->productVariants($entry->value('product_variants'));
        }

        if ($entry->has('stock') || $entry->originValues()->has('stock')) {
            $product->stock($entry->value('stock'));
        }

        if (SimpleCommerce::isUsingStandardTaxEngine() && ($entry->has('tax_category') || $entry->originValues()->has('tax_category'))) {
            $product->taxCategory($entry->value('tax_category'));
        }

        return $product->data(array_merge(
            Arr::except(
                $entry->values()->toArray(),
                ['price', 'product_variants', 'stock', 'tax_category']
            ),
            [
                'site' => optional($entry->site())->handle(),
                'slug' => $entry->slug(),
                'published' => $entry->published(),
            ]
        ));
    }

    public function make(): Product
    {
        return app(Product::class);
    }

    public function save(Product $product): void
    {
        $entry = $product->resource();

        if (! $entry) {
            $entry = Entry::make()
                ->id($product->id() ?? Stache::generateId())
                ->collection($this->collection);
        }

        if ($product->get('site')) {
            $entry->site($product->get('site'));
        }

        if ($product->get('slug')) {
            $entry->slug($product->get('slug'));
        }

        if ($product->get('published')) {
            $entry->published($product->get('published'));
        }

        $entry->data(
            array_merge(
                $product->data()->except(['id', 'site', 'slug', 'published'])->toArray(),
                [
                    'price' => $product->price(),
                    'product_variants' => $product->productVariants(),
                    'stock' => $product->stock(),
                    'tax_category' => SimpleCommerce::isUsingStandardTaxEngine() ? $product->taxCategory() : null,
                ]
            )
        );

        $entry->save();

        $product->id = $entry->id();
        $product->price = $entry->get('price');
        $product->productVariants = $entry->get('product_variants');
        $product->stock = $entry->get('stock');
        $product->taxCategory = $entry->get('tax_category');
        $product->data = $entry->data();
        $product->resource = $entry;
    }

    public function delete(Product $product): void
    {
        $product->resource()->delete();
    }

    public static function bindings(): array
    {
        return [];
    }
}
