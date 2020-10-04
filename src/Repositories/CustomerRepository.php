<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository as ContractsCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use Illuminate\Support\Str;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class CustomerRepository implements ContractsCustomerRepository
{
    public string $id = '';
    public string $title = '';
    public string $slug = '';
    public array $data = [];

    public function make(): self
    {
        $this->id = Stache::generateId();

        return $this;
    }

    public function find(string $id): self
    {
        $this->id = $id;

        $entry = $this->entry();

        if (! $entry) {
            throw new CustomerNotFound(__('simple-commerce::customers.customer_not_found', ['id' => $id]));
        }

        $this->data = $entry->data()->toArray();

        if ($entry->title === null || $entry->entry === '' || $entry->slug === null || $entry->slug === '') {
            $this->generateTitleAndSlug();
        } else {
            $this->title = $entry->title;
            $this->slug = $entry->slug;
        }

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

        return $this;
    }

    public function save(): self
    {
        $this->generateTitleAndSlug();

        Entry::make()
            ->collection(config('simple-commerce.collections.customers'))
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
        if ($mergeData) {
            $data = array_merge($this->data, $data);
        }

        if (! isset($data['title'])) {
            $this->generateTitleAndSlug();
            $data['title'] = $this->title;
        }

        $this
            ->entry()
            ->data($data)
            ->save();

        return $this;
    }

    public function entry(): EntriesEntry
    {
        $entry = Entry::find($this->id);

        if (!$entry) {
            throw new CustomerNotFound(__('simple-commerce::customers.customer_not_found', ['id' => $this->id]));
        }

        return $entry;
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'name'  => isset($this->data['name']) ? $this->data['name'] : null,
            'email' => isset($this->data['email']) ? $this->data['email'] : null,
        ];
    }

    public function generateTitleAndSlug(): self
    {
        $name = '';
        $email = '';

        if (isset($this->data['name'])) {
            $name = $this->data['name'];
        }

        if (isset($this->data['email'])) {
            $email = $this->data['email'];
        }

        $this->title = __('simple-commerce::customers.customer_entry_title', [
            'name' => $name,
            'email' => $email,
        ]);

        $this->slug = Str::slug($email);

        return $this;
    }
}
