<?php

namespace Damcclean\Commerce\Contracts;

interface CustomerRepository
{
    public function all();
    public function find($id);
    public function findBySlug(string $slug);
    public function findByEmail(string $email);
    public function findByStripeId(string $stripeId);
    public function make();
    public function query();
    public function save($entry);
    public function delete($entry);
    public function createRules($collection);
    public function updateRules($collection, $entry);
}
