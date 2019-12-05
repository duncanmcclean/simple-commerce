<?php

namespace Damcclean\Commerce\Stache\Repositories;

use Damcclean\Commerce\Contracts\OrderRepository as Contract;
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

    public function findBySlug(string $slug): Collection
    {
        return $this->query()->where('slug', $slug)->first();
    }

    public function findByEmail(string $email): Collection
    {
        return $this->query()->where('email', $email)->first();
    }

    public function findByStripeId(string $stripeId): Collection
    {
        return $this->query()->where('stripe_customer_id', $stripeId)->first();
    }

    public function save($entry)
    {
        if (! isset($entry['id'])) {
            $entry['id'] = (new Stache())->generateId();
        }

        $contents = Yaml::dumpFrontMatter($entry, null);
        file_put_contents($this->path.'/'.$entry['slug'].'.md', $contents);

        return $entry;
    }

    public function delete($entry)
    {
        return (new Filesystem())->delete($this->path.'/'.$entry.'.md');
    }

    public function query()
    {
        $files = File::allFiles($this->path);

        return collect($files)
            ->reject(function (SplFileInfo $file) {
                if ($file->getExtension() == 'md') {
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
            'status' => 'required|in:created,paid,cancelled,fulfilled,returned',
            'total' => 'required|integer',
            'shipping_address' => 'sometimes|address',
            'coupon' => 'sometimes|string',
            'stripe_customer_id' => 'required|string'
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'status' => 'required|in:created,paid,cancelled,fulfilled,returned',
            'total' => 'required|integer',
            'shipping_address' => 'sometimes|address',
            'coupon' => 'sometimes|string',
            'stripe_customer_id' => 'required|string'
        ];
    }
}
