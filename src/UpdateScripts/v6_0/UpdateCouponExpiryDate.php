<?php

namespace DuncanMcClean\SimpleCommerce\UpdateScripts\v6_0;

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use Illuminate\Support\Carbon;
use Statamic\UpdateScripts\UpdateScript;

class UpdateCouponExpiryDate extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        Coupon::all()
            ->filter(function ($coupon) {
                return $coupon->get('enabled') === false
                    && $coupon->get('expires_at') === null;
            })
            ->each(function ($coupon) {
                $coupon->set('expires_at', Carbon::now()->format('Y-m-d'))->save();
            });
    }
}
