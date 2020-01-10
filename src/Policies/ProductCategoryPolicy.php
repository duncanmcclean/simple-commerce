<?php

namespace Damcclean\Commerce\Policies;

use Damcclean\Commerce\Models\ProductCategory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Statamic\Auth\User;

class ProductCategoryPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->hasPermission('create product categories');
    }

    public function view(User $user, ProductCategory $category)
    {
        return $user->hasPermission('view product categories');
    }

    public function update(User $user, ProductCategory $category)
    {
        return $user->hasPermission('edit product categories');
    }

    public function delete(User $user, ProductCategory $category)
    {
        return $user->hasPermission('delete product categories');
    }
}
