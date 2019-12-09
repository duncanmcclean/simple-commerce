<?php

namespace Damcclean\Commerce\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class CouponPolicy
{
    use HandlesAuthorization;

    public function view($user, $coupon)
    {
        return $user->hasPermission('view coupons');
    }

    public function edit($user, $coupon)
    {
        return $user->hasPermission('edit coupons');
    }

    public function create($user, $coupon)
    {
        return $user->hasPermission('create coupons');
    }

    public function delete($user, $coupon)
    {
        return $user->hasPermission('delete coupons');
    }
}
