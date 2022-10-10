<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

class Response
{
    protected string $error = '';

    public function __construct(protected bool $success = false, protected array $data = [], protected string $checkoutUrl = '')
    {
    }

    public function success(): bool
    {
        return $this->success;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function checkoutUrl(): string
    {
        return $this->checkoutUrl;
    }

    public function error(string $errorMessage = '')
    {
        if ($errorMessage !== '') {
            $this->error = $errorMessage;

            return $this;
        }

        return $this->error;
    }
}
