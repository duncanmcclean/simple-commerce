<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Traits;

trait HasData
{
    public array $data = [];

    public function data(array $data = [])
    {
        if ($data === []) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]) && ! is_null($this->data[$key]);
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

    public function toArray(): array
    {
        return $this->data;
    }
}
