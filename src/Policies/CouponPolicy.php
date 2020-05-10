<?php

namespace DoubleThreeDigital\SimpleCommerce\Policies;

use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model as User;

class CouponPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->hasPermission('create coupons');
    }

    public function view(User $user)
    {
        return $user->hasPermission('view coupons');
    }

    public function update(User $user, Coupon $coupon)
    {
        return $user->hasPermission('edit coupons');
    }

    public function delete(User $user, Coupon $coupon)
    {
        return $user->hasPermission('delete coupons');
    }
}
