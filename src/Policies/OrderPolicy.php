<?php

namespace DuncanMcClean\SimpleCommerce\Policies;

class OrderPolicy
{
    public function index($user): bool
    {
        return $this->view($user);
    }

    public function view($user): bool
    {
        return $user->can('view orders');
    }

    public function edit($user): bool
    {
        return $user->can('view orders');
    }

    public function update($user): bool
    {
        return $user->can('edit orders');
    }

    public function delete($user): bool
    {
        return false;
    }
}
