<?php

namespace DoubleThreeDigital\SimpleCommerce\Data;

use Statamic\Support\Traits\FluentlyGetsAndSets;

trait HasData
{
    use FluentlyGetsAndSets;

    public function data($data = null)
    {
        return $this
            ->fluentlyGetOrSet('data')
            ->setter(function ($data) {
                if (is_array($data)) {
                    $data = collect($data);
                }

                return $data;
            })
            ->args(func_get_args());
    }

    public function has(string $key): bool
    {
        return $this->data()->has($key);
    }

    public function get(string $key, $default = null)
    {
        // For now, we want to fallback to properties with the same name if they exist..
        if (! $this->has($key) && property_exists($this, $key)) {
            return $this->{$key};
        }

        if (! $this->has($key) && $key === 'items' && property_exists($this, 'lineItems')) {
            return $this->lineItems->toArray();
        }

        return $this->data()->get($key, $default);
    }

    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function merge($data): self
    {
        $this->data = $this->data->merge($data);

        return $this;
    }

    public function toArray(): array
    {
        return $this->data()->toArray();
    }
}
