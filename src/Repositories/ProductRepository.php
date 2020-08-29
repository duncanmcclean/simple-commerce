<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\ProductRepository as ContractsProductRepository;
use DoubleThreeDigital\SimpleCommerce\Exceptions\ProductNotFound;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class ProductRepository implements ContractsProductRepository
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

        $this->title = $entry->title;
        $this->slug = $entry->slug();
        $this->data = $entry->data()->toArray();

        return $this;
    }

    public function title(string $title = ''): self
    {
        if ($title === '') {
            return $this->title;
        }

        $this->title = $title;

        return $this;
    }

    public function slug(string $slug = ''): self
    {
        if ($slug === '') {
            return $this->slug;
        }

        $this->title = $slug;

        return $this;
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
        Entry::make()
            ->collection(config('simple-commerce.collections.products'))
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
            $data = array_merge($data, $this->data);
        }

        $this->entry()
            ->data($data)
            ->save();

        return $this;
    }

    public function entry(): EntriesEntry
    {
        $entry = Entry::find($this->id);

        if (!$entry) {
            throw new ProductNotFound(__('simple-commerce::products.product_not_found', ['id' => $this->id]));
        }

        return $entry;
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'slug'  => $this->slug,
            'title' => $this->title,
            'price' => $this->data['price'],
        ];
    }
}
