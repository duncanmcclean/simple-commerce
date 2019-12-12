<?php

namespace Damcclean\Commerce\Stache\Repositories;

use Damcclean\Commerce\Contracts\CustomerRepository as Contract;
use Damcclean\Commerce\Models\File\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Statamic\Facades\YAML;
use Statamic\Stache\Stache;

class FileCustomerRepository implements Contract
{
    public function __construct()
    {
        $this->path = base_path().'/content/commerce/customers';
    }

    public function attributes($file): Collection
    {
        $attributes = Yaml::parse(file_get_contents($file));
        $attributes['slug'] = isset($attributes['slug']) ? $attributes['slug'] : str_replace('.md', '', basename($file));
        $attributes['edit_url'] = cp_route('customers.edit', ['customer' => $attributes['id']]);
        $attributes['delete_url'] = cp_route('customers.destroy', ['customer' => $attributes['id']]);

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

    public function findByEmail(string $email)
    {
        return $this->query()->where('email', $email)->first();
    }

    public function save($entry)
    {
        if (! isset($entry['id'])) {
            $entry['id'] = (new Stache())->generateId();
        }

        if (! isset($entry['slug'])) {
            $entry['slug'] = str_slug($entry['name']);
        }

        $item = new Customer($entry, $entry['slug']);
        $item->writeFile();

        return $item;
    }

    public function delete($entry)
    {
        $entry = $this->findBySlug($entry);

        return (new Customer([], $entry['slug']))->deleteFile();
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
            'name' => 'required|string',
            'email' => 'required|email',
            'address' => 'sometimes|string',
            'country' => 'sometimes|string',
            'zip_code' => 'sometimes|string',
            'card_brand' => 'string',
            'card_country' => 'string',
            'card_expiry_month' => 'string',
            'card_expiry_year' => 'string',
            'card_last_four' => 'string',
            'currency' => 'required|string'
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
            'address' => 'sometimes|string',
            'country' => 'sometimes|string',
            'zip_code' => 'sometimes|string',
            'card_brand' => 'string',
            'card_country' => 'string',
            'card_expiry_month' => 'string',
            'card_expiry_year' => 'string',
            'card_last_four' => 'string',
            'currency' => 'required|string'
        ];
    }

    public function update($id, $entry)
    {
        $slug = $this->find($id)['slug'];

        $item = new Customer(collect($entry)->toArray(), $slug);
        $item->writeFile();

        return $item->data;
    }
}
