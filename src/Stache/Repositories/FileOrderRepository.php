<?php

namespace Damcclean\Commerce\Stache\Repositories;

use Damcclean\Commerce\Contracts\OrderRepository as Contract;
use Damcclean\Commerce\Models\File\Order;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Statamic\Facades\YAML;
use Statamic\Stache\Stache;

class FileOrderRepository implements Contract
{
    public function __construct()
    {
        $this->path = config('commerce.storage.orders.files');

        if (! file_exists($this->path)) {
            (new Filesystem())->makeDirectory($this->path);
        }
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
                return collect(Yaml::parse(file_get_contents($file)));
            });
    }

    public function createRules()
    {
        return [
            'total' => ['required', 'regex:/^\d*(\.\d{2})?$/'],
            'notes' => '',
            'products.*.product' => 'required',
            'products.*.quantity' => 'required',

            'address' => 'sometimes|string',
            'country' => 'sometimes|string',
            'zip_code' => 'sometimes|string',

            'status' => 'required|in:created,paid,cancelled,fulfilled,returned',
            'coupon' => '',
            'customer' => 'required',
            'order_date' => 'required',
        ];
    }

    public function updateRules($entry)
    {
        return [
            'total' => ['required', 'regex:/^\d*(\.\d{2})?$/'],
            'notes' => '',
            'products.*.product' => 'required',
            'products.*.quantity' => 'required',

            'address' => 'sometimes|string',
            'country' => 'sometimes|string',
            'zip_code' => 'sometimes|string',

            'status' => 'required|in:created,paid,cancelled,fulfilled,returned',
            'coupon' => '',
            'customer' => 'required',
            'order_date' => 'required',
        ];
    }

    public function update($id, $entry)
    {
        $slug = $this->find($id)['slug'];

        $item = new Order(collect($entry)->toArray(), $slug);
        $item->writeFile();

        return $item->data;
    }
}
