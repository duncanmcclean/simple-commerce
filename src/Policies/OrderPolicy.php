<?php

namespace DuncanMcClean\SimpleCommerce\Policies;

use Statamic\Facades\User;

class OrderPolicy
{
    public function index($user)
    {
        return true; // todo
    }

    public function create($user)
    {
        return true; // todo
    }

    public function store($user)
    {
        return true; // todo
    }

    public function edit($user)
    {
        return true; // todo
    }

    public function update($user)
    {
        return true; // todo
    }

    public function delete($user)
    {
        return false; // todo
    }
}