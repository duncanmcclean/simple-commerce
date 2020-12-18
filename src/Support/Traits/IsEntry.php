<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Traits;

use Statamic\Facades\Entry as EntryAPI;
use Statamic\Facades\Site as SiteAPI;
use Statamic\Facades\Stache;
use Statamic\Sites\Site;

trait IsEntry
{
    protected $entry;
    protected $collection;

    public function all()
    {
        return $this->query()->all();
    }

    public function query()
    {
        return EntryAPI::whereCollection($this->collection());
    }

    public function find(string $id): self
    {
        $this->entry = EntryAPI::find($id);

        if ($this->entry->existsIn(SiteAPI::current()->handle()) && $this->entry->locale() !== SiteAPI::current()->handle()) {
            $this->entry = $this->entry->in(SiteAPI::current()->handle());
        }

        $this->id    = $this->entry->id;
        $this->title = $this->entry->get('title');
        $this->slug  = $this->entry->slug();
        $this->data  = $this->entry->data()->toArray();

        return $this;
    }

    public function create(array $data): self
    {
        $this->id   = ! is_null($this->id) ? $this->id : Stache::generateId();
        $this->site = SiteAPI::current()->handle();
        $this->slug = ! is_null($this->slug) ? $this->slug : '';
        $this->published = ! is_null($this->published) ? $this->published : false;

        if (! is_null($this->data)) {
            $data = array_merge($data, $this->data);
        }

        $this->data($data);

        $this->save();

        return $this;
    }

    public function save(): self
    {
        if (! $this->entry) {
            $this->entry = EntryAPI::make();
        }

        $this->entry
            ->id($this->id)
            ->site($this->site)
            ->slug($this->slug)
            ->published($this->published)
            ->data($this->data);

        $this->entry->save();

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete()
    {
        $this->entry->delete();
    }

    public function collection(): string
    {
        return '';
    }

    public function entry()
    {
        return $this->entry;
    }

    public function title(string $title = '')
    {
        if ($title !== '') {
            $this->title = $title;

            return $this;
        }

        return $this->title;
    }

    public function slug(string $slug = '')
    {
        if ($slug !== '') {
            $this->slug = $slug;

            return $this;
        }

        return $this->slug;
    }

    public function site($site = null): self
    {
        if (is_null($site)) {
            return $this->site;
        }

        if (! $site instanceof Site) {
            $site = SiteAPI::get($site);
        }

        $this->site = $site;

        return $this;
    }

    // TODO: remove
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

    // TODO: refactor to property hooks
    public function afterSaved()
    {
        return null;
    }
}
