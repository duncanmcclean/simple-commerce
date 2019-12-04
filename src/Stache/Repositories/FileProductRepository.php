<?php

namespace Damcclean\Commerce\Stache\Repositories;

use Damcclean\Commerce\Contracts\ProductRepository as Contract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\File;
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
        $attributes['edit_url'] = cp_route("$this->route.edit", ['product' => $attributes['slug']]);
        $attributes['delete_url'] = cp_route("$this->route.destroy", ['product' => $attributes['slug']]);

        return $attributes;
    }

    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function find($id): Collection
    {
        return $this->query()->where('id', $id)->first();
    }

    public function findBySlug(string $slug): Collection
    {
        return $this->query()->where('slug', $slug)->first();
    }

    public function save($entry)
    {
        dd($entry);

        if (! $entry->id()) {
            $entry->id((new Stache())->generateId());
        }

        $contents = Yaml::dumpFrontMatter($entry, null);
        return file_put_contents($this->path.'/'.$entry->slug.'.md', $contents);
    }

    public function delete($entry)
    {
        dd($entry);
        return (new Filesystem())->delete($this->path.'/'.$entry.'.md');
    }

    public function query()
    {
        $files = File::getFilesByType($this->path, 'md');

        return collect($files)
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
