<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Traits;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer;
use DoubleThreeDigital\SimpleCommerce\Exceptions\EntryNotFound;
use Illuminate\Support\Arr;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Facades\Site as SiteAPI;
use Statamic\Facades\Stache;
use Statamic\Fields\Blueprint;
use Statamic\Http\Resources\API\EntryResource;
use Statamic\Sites\Site;
use Statamic\Support\Traits\FluentlyGetsAndSets;

trait IsEntry
{
    use FluentlyGetsAndSets;

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
            throw new EntryNotFound("Entry [{$id}] could not be found.");
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
        $this->entry = null;

        $this->id = !is_null($this->id) ? $this->id : Stache::generateId();
        $this->site = $site !== '' ? $site : SiteAPI::current()->handle();
        $this->slug = !is_null($this->slug) ? $this->slug : '';
        $this->published = !is_null($this->published) ? $this->published : false;

        if (! $this->slug && isset($data['slug'])) {
            $this->slug = $data['slug'];
        }

        if (! $this->published && isset($data['published'])) {
            $this->published = $data['published'];
        }

        $data = array_merge($data, $this->defaultFieldsInBlueprint());

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

        ray($this);

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
        return $this
            ->fluentlyGetOrSet('title')
            ->args(func_get_args());
    }

    public function slug(string $slug = null)
    {
        return $this
            ->fluentlyGetOrSet('slug')
            ->args(func_get_args());
    }

    public function site($site = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->setter(function ($site) {
                if (! $site instanceof Site) {
                    return SiteAPI::get($site);
                }

                return $site;
            })
            ->getter(function ($site) {
                return $site;
            })
            ->args(func_get_args());
    }

    public function fresh(): self
    {
        return $this->find($this->id);
    }

    public function blueprint(): Blueprint
    {
        if (! $this->entry) {
            return Collection::find($this->collection())->entryBlueprint();
        }

        return $this->entry()->blueprint();
    }

    protected function defaultFieldsInBlueprint(): array
    {
        return $this->blueprint()->fields()->items()
            ->where('field.default', '!==', null)
            ->mapWithKeys(function ($field) {
                return [$field['handle'] => $field['field']['default']];
            })
            ->toArray();
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
