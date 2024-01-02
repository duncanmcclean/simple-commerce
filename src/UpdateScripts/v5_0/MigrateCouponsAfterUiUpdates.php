<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v5_0;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as CouponContract;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Statamic\UpdateScripts\UpdateScript;

class MigrateCouponsAfterUiUpdates extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.9.0');
    }

    public function update()
    {
        Coupon::all()
            ->filter(function (CouponContract $coupon) {
                return $coupon->has('customers');
            })
            ->each(function (CouponContract $coupon) {
                $coupon
                    ->set('customer_eligibility', 'specific_customers')
                    ->save();
            });
    }
}
