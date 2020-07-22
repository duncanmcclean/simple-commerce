<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository as ContractsCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Illuminate\Support\Str;

class CustomerRepository implements ContractsCustomerRepository
{
    public string $id;
    public string $title;
    public string $slug;
    public array $data;

    public function make(): self
    {
        $this->id = Stache::generateId();

        return $this;
    }

    public function find(string $id): self
    {
        $this->id = $id;

        $entry = $this->entry();

        $this->title = $entry->title;
        $this->slug = $entry->slug();
        $this->data = $entry->data()->toArray();

        return $this;
    }

    public function findByEmail(string $email): self
    {
        $entry = Entry::query()
            ->where('collection', config('simple-commerce.collections.customers'))
            ->where('slug', Str::slug($email))
            ->first();

        if (! $entry) {
            throw new CustomerNotFound(__('simple-commerce::customers.customer_not_found_by_email', ['email' => $email]));
        }

        return $this->find($entry->id());
    }

    public function data(array $data = []): self
    {
        if ($data === []) {
            return $this->data;
        }

        $this->data = $data;
        $this->generateTitleAndSlug();

        return $this;
    }

    public function save(): self
    {
        Entry::make()
            ->collection(config('simple-commerce.collections.customers'))
            ->blueprint('customer')
            ->locale(Site::current()->handle())
            ->published(false)
            ->slug($this->slug)
            ->id($this->id)
            ->data(array_merge($this->data, [
                'title' => $this->title,
            ]))
            ->save();

        return $this;    
    }

    public function update(array $data, bool $mergeData = true): self
    {
        $entry = $this->entry();

        if ($mergeData) {
            $data = array_merge($entry->data()->toArray(), $data);
        }

        $entry
            ->data($data)
            ->save();

        return $this;
    }

    public function entry()
    {
        $entry = Entry::find($this->id);

        if (! $entry) {
            throw new CustomerNotFound(__('simple-commerce::customers.customer_not_found', ['id' => $this->id]));
        }

        return $entry;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'name' => isset($this->data['name']) ? $this->data['name'] : null,
            'email' => isset($this->data['email']) ? $this->data['email'] : null,
        ];
    }

    protected function generateTitleAndSlug(): self
    {
        $name = $this->data['name'];
        $email = $this->data['email'];

        $this->title = "$name <$email>";
        $this->slug = Str::slug($email);

        return $this;
    }
}