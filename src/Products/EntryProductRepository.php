<?php

namespace DoubleThreeDigital\SimpleCommerce\Products;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use DoubleThreeDigital\SimpleCommerce\Contracts\ProductRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\ProductNotFound;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
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
        return Entry::whereCollection($this->collection)->all();
    }

    public function find($id): ?Product
    {
        $entry = Entry::find($id);

        if (! $entry) {
            throw new ProductNotFound("Product [{$id}] could not be found.");
        }

        $product = app(Product::class)
            ->resource($entry)
            ->id($entry->id());

        if ($entry->has('price')) {
            $product->price($entry->get('price'));
        }

        if ($entry->has('product_variants')) {
            $product->productVariants($entry->get('product_variants'));
        }

        if ($entry->has('stock')) {
            $product->stock($entry->get('stock'));
        }

        if (SimpleCommerce::isUsingStandardTaxEngine() && $entry->has('tax_category')) {
            $product->taxCategory($entry->get('tax_category'));
        }

        return $product->data(array_merge(
            Arr::except(
                $entry->data()->toArray(),
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

    public function save($product): void
    {
        $entry = $product->resource();

        if (! $entry) {
            $entry = Entry::make()
                ->id(Stache::generateId())
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

    public function delete($product): void
    {
        $product->resource()->delete();
    }

    // protected function isUsingEloquentDriverWithIncrementingIds(): bool
    // {
    //     return config('statamic.eloquent-driver.entries.model') === \Statamic\Eloquent\Entries\EntryModel::class;
    // }

    public static function bindings(): array
    {
        return [];
    }
}
