<?php

namespace DuncanMcClean\SimpleCommerce\Policies;

class CouponPolicy
{
    public function index($user): bool
    {
        return $this->view($user);
    }

    public function view($user): bool
    {
        return $user->can('view coupons');
    }

    public function create($user): bool
    {
        return $user->can('create coupons');
    }

    public function store($user): bool
    {
        return $user->can('create coupons');
    }

    public function edit($user, $coupon): bool
    {
        return $user->can('view coupons');
    }

    public function update($user, $coupon): bool
    {
        return $user->can('edit coupons');
    }

    public function delete($user, $coupon): bool
    {
        return $user->can('delete coupons');
    }
}
