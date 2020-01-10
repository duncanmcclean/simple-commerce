<?php

namespace Damcclean\Commerce\Policies;

use Damcclean\Commerce\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;
use Statamic\Auth\User;

class ProductPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->hasPermission('create products');
    }

    public function view(User $user, Product $product)
    {
        return $user->hasPermission('view products');
    }

    public function update(User $user, Product $product)
    {
        return $user->hasPermission('edit products');
    }

    public function delete(User $user, Product $product)
    {
        return $user->hasPermission('delete products');
    }
}
