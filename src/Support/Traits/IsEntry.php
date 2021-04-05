<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Traits;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer;
use DoubleThreeDigital\SimpleCommerce\Exceptions\EntryNotFound;
use Illuminate\Support\Arr;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Facades\Site as SiteAPI;
use Statamic\Facades\Stache;
use Statamic\Http\Resources\API\EntryResource;
use Statamic\Sites\Site;

trait IsEntry
{
    public function all()
    {
        return $this->query()->all();
    }

    public function query()
    {
        return EntryAPI::whereCollection($this->collection());
    }

    public function find($id): self
    {
        $this->entry = EntryAPI::find((string) $id);

        if (!$this->entry) {
            throw new EntryNotFound("Entry could not be found: {$id}");
        }

        if ($this->entry->existsIn(SiteAPI::current()->handle()) && $this->entry->locale() !== SiteAPI::current()->handle()) {
            $this->entry = $this->entry->in(SiteAPI::current()->handle());
        }

        $this->id = $this->entry->id();
        $this->title = $this->entry->data()->get('title');
        $this->slug = $this->entry->slug();
        $this->site = $this->entry()->locale();
        $this->data = $this->entry->data()->toArray();
        $this->published = $this->entry->published();

        return $this;
    }

    public function create(array $data = [], string $site = ''): self
    {
        $this->id = !is_null($this->id) ? $this->id : Stache::generateId();
        $this->site = $site !== '' ? $site : SiteAPI::current()->handle();
        $this->slug = !is_null($this->slug) ? $this->slug : '';
        $this->published = !is_null($this->published) ? $this->published : false;

        $this->data(
            Arr::except($data, ['id', 'site', 'slug', 'publish'])
        );

        $this->save();

        return $this;
    }

    public function save(): self
    {
        if (!$this->entry) {
            $this->entry = EntryAPI::make()
                ->id($this->id)
                ->locale($this->site);
        }

        if ($this instanceof Customer) {
            $this->generateTitleAndSlug();
        }

        if ($this->title) {
            $data['title'] = $this->title;
        }

        $this->entry
            ->collection($this->collection())
            ->slug($this->slug)
            ->published($this->published)
            ->data($this->data);

        if (method_exists($this, 'beforeSaved')) {
            $this->beforeSaved();
        }

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

    public function entry(): Entry
    {
        return $this->entry;
    }

    public function toResource()
    {
        return new EntryResource($this->entry);
    }

    public function toAugmentedArray($keys = null)
    {
        return $this->entry()->toAugmentedArray($keys);
    }

    public function id()
    {
        return $this->id;
    }

    public function title(string $title = null)
    {
        if (is_null($title)) {
            return $this->title;
        }

        $this->title = $title;

        return $this;
    }

    public function slug(string $slug = null)
    {
        if (is_null($slug)) {
            return $this->slug;
        }

        $this->slug = $slug;

        return $this;
    }

    public function site($site = null): self
    {
        if (is_null($site)) {
            return $this->site;
        }

        if (!$site instanceof Site) {
            $site = SiteAPI::get($site);
        }

        $this->site = $site;

        return $this;
    }

    public function fresh(): self
    {
        return $this->find($this->id);
    }

    public function beforeSaved()
    {
        return null;
    }

    public function afterSaved()
    {
        return null;
    }
}
