<?php

namespace Damcclean\Commerce\Contracts;

interface ProductRepository
{
    public function all();
    public function find($id);
    public function findBySlug(string $slug);
    public function query();
    public function save($entry);
    public function delete($entry);
    public function createRules();
    public function updateRules($entry);
    public function update($id, $entry);
}
