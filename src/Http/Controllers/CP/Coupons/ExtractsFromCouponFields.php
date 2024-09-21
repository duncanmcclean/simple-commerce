<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Coupons;

trait ExtractsFromCouponFields
{
    protected function extractFromFields($coupon, $blueprint)
    {
        $values = $coupon->data()
            ->merge([
                'code' => $coupon->code(),
                'type' => $coupon->type(),
                'amount' => $coupon->amount(),
            ]);

        $fields = $blueprint
            ->fields()
            ->setParent($coupon)
            ->addValues($values->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}