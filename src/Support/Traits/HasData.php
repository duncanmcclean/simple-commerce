<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Traits;

use Statamic\Support\Traits\FluentlyGetsAndSets;

trait HasData
{
    use FluentlyGetsAndSets;

    public function data($data = null)
    {
        return $this
            ->fluentlyGetOrSet('data')
            ->setter(function ($data) {
                return array_merge($this->data, $data);
            })
            ->getter(function ($data) {
                return collect($data);
            })
            ->args(func_get_args());
    }

    public function has(string $key): bool
    {
        return $this->data()->has($key);
    }

    public function get(string $key)
    {
        return $this->data()->get($key);
    }

    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        $this->entry()->set($key, $value)->save();

        return $this;
    }

    public function toArray(): array
    {
        return $this->data()->toArray();
    }
}
