<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Gateway
{
    public static $name;
    public static $description;

    public function prepare(array $data);
    public function purchase(array $data): array;
    public function purchaseRules(): array;
    public function getCharge(array $data): array;
    public function refundCharge(array $data): array;
}