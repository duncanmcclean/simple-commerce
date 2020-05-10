<?php

namespace DoubleThreeDigital\SimpleCommerce\Policies;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\User;

class OrderPolicy
{
    use HandlesAuthorization;

    public function view(User $user)
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
