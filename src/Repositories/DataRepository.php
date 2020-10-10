<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use Exception;
use Statamic\Contracts\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Fields\Blueprint;

trait DataRepository
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

    public function update(array $data, bool $mergeData = true): self
    {
        if ($mergeData) {
            $data = array_merge($this->data, $data);
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
            throw new Exception("Entry could not be found. ID: {$this->id}");
        }

        return $entry;
    }

    public function toArray(): array
    {
        return [];
    }

    public function get(string $key)
    {
        return $this->data[$key];
    }

    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        $this->entry()->set($key, $value)->save();

        return $this;
    }

    public static function bindings(): array
    {
        return [];
    }
}
