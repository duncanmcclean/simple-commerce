<?php

namespace DoubleThreeDigital\SimpleCommerce\Policies;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model as User;

class ProductPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->hasPermission('create products');
    }

    public function view(User $user)
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
