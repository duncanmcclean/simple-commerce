<?php

namespace Damcclean\Commerce\Stache\Repositories;

use Damcclean\Commerce\Contracts\ProductRepository as Contract;
use Damcclean\Commerce\Models\File\Product;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Statamic\Facades\YAML;
use Statamic\Stache\Stache;

class FileProductRepository implements Contract
{
    public function __construct()
    {
        $this->path = base_path().'/content/commerce/products';

        if (! file_exists($this->path)) {
            (new Filesystem())->makeDirectory($this->path);
        }
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

        $item = new Product($entry, $entry['slug']);
        $item->writeFile();

        return $item;
    }

    public function delete($entry)
    {
        $entry = $this->findBySlug($entry);

        return (new Product([], $entry['slug']))->deleteFile();
    }

    public function query()
    {
        $files = File::allFiles($this->path);

        return collect($files)
            ->reject(function (SplFileInfo $file) {
                if ($file->getExtension() == 'yaml') {
                    return false;
                }

                return true;
            })
            ->map(function ($file) {
                return $this->attributes($file);
            });
    }

    public function createRules()
    {
        return [
            'title' => 'required|string',
            'slug' => 'required|string',
            'publish_date' => '',
            'expiry_date' => '',
            'enabled' => 'boolean',
            'free_shipping' => 'boolean',
            'shipping_price' => ['sometimes', 'required_if:free_shipping,false', 'regex:/^\d*(\.\d{2})?$/'],
            'price' => ['required', 'regex:/^\d*(\.\d{2})?$/'],
            'stock_number' => 'sometimes|integer',
        ];
    }

    public function updateRules($entry)
    {
        return [
            'title' => 'required|string',
            'slug' => 'required|string',
            'publish_date' => '',
            'expiry_date' => '',
            'enabled' => 'boolean',
            'free_shipping' => 'boolean',
            'shipping_price' => ['regex:/^\d*(\.\d{2})?$/'],
            'price' => ['required', 'regex:/^\d*(\.\d{2})?$/'],
            'stock_number' => 'sometimes|integer',
        ];
    }

    public function update($id, $entry)
    {
        $slug = $this->find($id)['slug'];

        $item = new Product(collect($entry)->toArray(), $slug);
        $item->writeFile();

        return $item->data;
    }
}
