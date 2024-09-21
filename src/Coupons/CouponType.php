<?php

namespace DuncanMcClean\SimpleCommerce\Coupons;

enum CouponType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public static function label($status): string
    {
        return match ($status) {
            self::Fixed => __('Fixed Discount'),
            self::Percentage => __('Percentage Discount'),
        };
    }
}
