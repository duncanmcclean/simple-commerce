<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Traits;

trait HasData
{
    public function data($data = null)
    {
        if ($data === null) {
            return $this->data;
        }

        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key])
            && !is_null($this->data[$key]);
    }

    public function get(string $key)
    {
        if (!$this->has($key)) {
            return null;
        }

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
