<?php

namespace Damcclean\Commerce\Stache\Repositories;

use Damcclean\Commerce\Contracts\OrderRepository as Contract;
use Damcclean\Commerce\Models\File\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Statamic\Facades\YAML;
use Statamic\Stache\Stache;

class FileOrderRepository implements Contract
{
    public function __construct()
    {
        $this->path = base_path().'/content/commerce/orders';
    }

    public function attributes($file): Collection
    {
        $attributes = Yaml::parse(file_get_contents($file));
        $attributes['slug'] = isset($attributes['slug']) ? $attributes['slug'] : str_replace('.md', '', basename($file));
        $attributes['edit_url'] = cp_route('orders.edit', ['order' => $attributes['id']]);
        $attributes['delete_url'] = cp_route('orders.destroy', ['order' => $attributes['id']]);

        return collect($attributes);
    }

    public function all(): Collection
    {
        return $this->query();
    }

    public function find($id): Collection
    {
        return $this->query()->where('id', $id)->first();
    }

    public function findBySlug(string $slug)
    {
        return $this->query()->where('slug', $slug)->first();
    }

    public function save($entry)
    {
        if (! isset($entry['id'])) {
            $entry['id'] = (new Stache())->generateId();
        }

        if (! isset($entry['slug'])) {
            $entry['slug'] = uniqid();
        }

        $item = (new Order($entry, $entry['slug']));
        $item->writeFile();

        return $item;
    }

    public function delete($entry)
    {
        $entry = $this->findBySlug($entry);

        return (new Order([], $entry['slug']))->deleteFile();
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
            'status' => 'required|in:created,paid,cancelled,fulfilled,returned',
            'total' => ['required', 'regex:/^\d*(\.\d{2})?$/'],
            'shipping_address' => 'sometimes|address',
            'coupon' => 'sometimes|string',
            'stripe_customer_id' => 'required|string'
        ];
    }

    public function updateRules($entry)
    {
        return [
            'status' => 'required|in:created,paid,cancelled,fulfilled,returned',
            'total' => ['required', 'regex:/^\d*(\.\d{2})?$/'],
            'shipping_address' => 'sometimes|address',
            'coupon' => 'sometimes|string',
            'stripe_customer_id' => 'required|string'
        ];
    }

    public function update($id, $entry)
    {
        $slug = $this->find($id)['slug'];

        $item = new Order($entry->toArray(), $slug);
        $item->writeFile();

        return $item->data;
    }
}
