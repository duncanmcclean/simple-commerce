<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Contracts\Products\Product;
use DuncanMcClean\SimpleCommerce\Contracts\Products\ProductRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Exceptions\ProductNotFound;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class ProductRepository implements RepositoryContract
{
    protected $collection;

    public function __construct()
    {
        $this->collection = 'products'; // todo: make this configurable
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(QueryBuilder::class, [
            'store' => app('stache')->store('entries'),
        ])->where('collection', $this->collection);
    }

    public function find($id): ?Product
    {
        $product = $this->query()->where('id', $id)->first();

        if (! $product) {
            return null;
        }

        return $product;
    }

    public function findOrFail($id): Product
    {
        $product = $this->find($id);

        if (! $product) {
            throw new ProductNotFound("Product [{$id}] could not be found.");
        }

        return $product;
    }

    public function make(): Product
    {
        return app(Product::class);
    }

    public function save(Product $product): void
    {
        $entry = $product->entry();

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
//                    'tax_category' => SimpleCommerce::isUsingStandardTaxEngine() ? $product->taxCategory() : null,
                ]
            )
        );

        $entry->save();

        $product->id = $entry->id();
        $product->price = $entry->get('price');
        $product->productVariants = $entry->get('product_variants');
        $product->stock = $entry->get('stock');
        $product->data = $entry->data();
        $product->entry = $entry;
    }

    public function delete(Product $product): void
    {
        $product->entry()->delete();
    }

    public static function bindings(): array
    {
        return [
            Product::class => \DuncanMcClean\SimpleCommerce\Products\Product::class,
        ];
    }
}
