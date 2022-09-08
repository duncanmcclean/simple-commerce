<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

enum CouponType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
}
