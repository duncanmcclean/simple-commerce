<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

class GatewayResponse
{
    protected bool $success = false;
    protected array $data = [];
    protected string $error = '';

    public function __construct(bool $success = false, array $data = [])
    {
        $this->success = $success;
        $this->data = $data;
    }

    public function success()
    {
        return $this->success;
    }

    public function data()
    {
        return $this->data;
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
