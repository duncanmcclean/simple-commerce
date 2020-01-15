<?php

namespace DoubleThreeDigital\SimpleCommerce\Policies;

use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Statamic\Auth\User;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->hasPermission('create customers');
    }

    public function view(User $user, Customer $customer)
    {
        return $user->hasPermission('view customers');
    }

    public function update(User $user, Customer $customer)
    {
        return $user->hasPermission('edit customers');
    }

    public function delete(User $user, Customer $customer)
    {
        return $user->hasPermission('delete customer');
    }
}
