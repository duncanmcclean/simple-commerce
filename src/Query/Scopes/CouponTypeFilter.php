<?php

namespace DuncanMcClean\SimpleCommerce\Query\Scopes;

use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use Statamic\Query\Scopes\Filter;

class CouponTypeFilter extends Filter
{
    public $pinned = true;

    public static $title = 'Type';

    public function fieldItems()
    {
        return [
            'type' => [
                'type' => 'radio',
                'options' => collect(CouponType::cases())->mapWithKeys(fn ($case) => [
                    $case->value => $case->name,
                ])->toArray(),
            ],
        ];
    }

    public function apply($query, $values)
    {
        return $query->where('type', $values['type']);
    }

    public function badge($values)
    {
        $couponTypeLabel = CouponType::from($values['type'])->name;

        return "Type: {$couponTypeLabel}";
    }

    public function visibleTo($key)
    {
        return $key === 'simple-commerce.coupons';
    }
}
