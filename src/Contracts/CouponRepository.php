<?php

namespace Damcclean\Commerce\Contracts;

interface CouponRepository
{
    public function all();
    public function find($id);
    public function findBySlug(string $slug);
    public function make();
    public function query();
    public function save($entry);
    public function delete($entry);
    public function createRules($collection);
    public function updateRules($collection, $entry);
}
