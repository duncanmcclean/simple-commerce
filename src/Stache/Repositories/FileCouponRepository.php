<?php

namespace Damcclean\Commerce\Stache\Repositories;

use Damcclean\Commerce\Contracts\CouponRepository as Contract;
use Damcclean\Commerce\Models\File\Coupon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Statamic\Facades\YAML;
use Statamic\Stache\Stache;

class FileCouponRepository implements Contract
{
    public function __construct()
    {
        $this->path = config('commerce.storage.coupons.files');

        if (! file_exists($this->path)) {
            (new Filesystem())->makeDirectory($this->path);
        }
    }

    public function attributes($file): Collection
    {
        $attributes = Yaml::parse(file_get_contents($file));
        $attributes['slug'] = isset($attributes['slug']) ? $attributes['slug'] : str_replace('.md', '', basename($file));
        $attributes['edit_url'] = cp_route('coupons.edit', ['coupon' => $attributes['id']]);
        $attributes['delete_url'] = cp_route('coupons.destroy', ['coupon' => $attributes['id']]);

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

    public function save($entry)
    {
        if (! isset($entry['id'])) {
            $entry['id'] = (new Stache())->generateId();
        }

        if (! isset($entry['slug'])) {
            $entry['slug'] = str_slug($entry['title']);
        }

        $item = new Coupon($entry, $entry['slug']);
        $item->writeFile();

        return $item;
    }

    public function delete($entry)
    {
        $entry = $this->findBySlug($entry);

        return (new Coupon([], $entry['slug']))->deleteFile();
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

    public function createRules($collection)
    {
        return [
            'title' => 'required|string',
            'description' => 'sometimes|string',
            'code' => 'required|string',
            'effect' => 'required|in:percentage,fixed,amount',
            'amount' => 'required|integer',
            'enabled' => 'boolean',
            'start_date' => '',
            'end_date' => '',
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'title' => 'required|string',
            'description' => 'sometimes|string',
            'code' => 'required|string',
            'effect' => 'required|in:percentage,fixed,amount',
            'amount' => 'required|integer',
            'enabled' => 'boolean',
            'start_date' => '',
            'end_date' => '',
        ];
    }

    public function update($id, $entry)
    {
        $slug = $this->find($id)['slug'];

        $item = new Coupon(collect($entry)->toArray(), $slug);
        $item->writeFile();

        return $item->data;
    }
}
