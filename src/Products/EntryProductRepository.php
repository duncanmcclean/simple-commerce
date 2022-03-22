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

        return app(Product::class)
            ->entry($entry)
            ->id($entry->id())
            ->data(array_merge(
                $entry->data()->toArray(),
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
        $entry = $product->entry();

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
            Arr::except($product->data(), ['id', 'site', 'slug', 'published'])
        );

        $entry->save();

        $product->id = $entry->id();
        $product->entry = $entry;
    }

    public function delete($product): void
    {
        $product->entry()->delete();
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
