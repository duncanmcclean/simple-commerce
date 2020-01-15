<?php

namespace Damcclean\Commerce\Policies;

use Damcclean\Commerce\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;
use Statamic\Auth\User;

class OrderPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->hasPermission('create orders');
    }

    public function view(User $user, Order $order)
    {
        return $user->hasPermission('view orders');
    }

    public function update(User $user, Order $order)
    {
        return $user->hasPermission('edit orders');
    }

    public function delete(User $user, Order $order)
    {
        return $user->hasPermission('delete orders');
    }

    public function refund(User $user, Order $order)
    {
        return $user->hasPermission('refund orders');
    }
}
