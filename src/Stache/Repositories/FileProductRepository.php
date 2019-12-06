<?php

namespace Damcclean\Commerce\Stache\Repositories;

use Damcclean\Commerce\Contracts\ProductRepository as Contract;
use Damcclean\Commerce\ProductItem;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use SplFileInfo;
use Illuminate\Support\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Stache\Stache;

class FileProductRepository implements Contract
{
    public function __construct()
    {
        $this->path = base_path().'/content/commerce/products';
    }

    public function attributes($file): Collection
    {
        $attributes = Yaml::parse(file_get_contents($file));
        $attributes['slug'] = isset($attributes['slug']) ? $attributes['slug'] : str_replace('.md', '', basename($file));
        $attributes['edit_url'] = cp_route('products.edit', ['product' => $attributes['id']]);
        $attributes['delete_url'] = cp_route('products.destroy', ['product' => $attributes['id']]);

        return collect($attributes);
    }

    public function all(): Collection
    {
        return $this->query();
    }

    public function find($id)
    {
        //dd($this->query()->where('id', $id)->first());

        return $this->query()->where('id', $id)->first();
    }

    public function findBySlug(string $slug): Collection
    {
        return $this->query()->where('slug', $slug)->first();
    }

    public function save($entry)
    {
        if (! isset($entry['id'])) {
            $entry['id'] = (new Stache())->generateId();
        }

        if (! isset($entry['slug'])) {
            $entry['slug'] = str_slug($entry['title']);
        }

        $item = new ProductItem($entry, $entry['slug']);
        $item->writeFile();

        return $item;
    }

    public function delete($entry)
    {
        //return (new ProductItem([], ))->deleteFile();

        $entry = $this->findBySlug($entry);

        return (new ProductItem([], $entry['slug']))->deleteFile();
    }

    public function query()
    {
        $files = File::allFiles($this->path);

        return collect($files)
            ->reject(function (SplFileInfo $file) {
                if ($file->getExtension() == 'md' || $file->getExtension() == 'yaml') {
                    return false;
                }

                return true;
            })
            ->map(function ($file) {
                return $this->attributes($file);
            });
    }

    public function make(): Collection
    {
        //
    }

    public function createRules($collection)
    {
        return [
            'title' => 'required|string',
            'description' => 'sometimes|string',
            'slug' => 'required|string',
            'publish_date' => '',
            'expiry_date' => '',
            'enabled' => 'boolean',
            'free_shipping' => 'boolean',
            'price' => 'required|integer',
            'shipping_price' => 'sometimes|integer',
            'stock_number' => 'sometimes|integer'
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'title' => 'required|string',
            'description' => 'sometimes|string',
            'slug' => 'required|string',
            'publish_date' => '',
            'expiry_date' => '',
            'enabled' => 'boolean',
            'free_shipping' => 'boolean',
            'price' => 'required|integer',
            'shipping_price' => 'sometimes|integer',
            'stock_number' => 'sometimes|integer'
        ];
    }
}
