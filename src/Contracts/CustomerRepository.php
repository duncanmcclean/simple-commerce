<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

interface CustomerRepository
{
    public function all();

    public function query();

    public function find($id): ?Customer;

    public function findOrFail($id): Customer;

    public function findByEmail(string $email): ?Customer;

    public function make(): Customer;

    public function save(Customer $customer): void;

    public function delete(Customer $customer): void;

    public static function bindings(): array;
}
