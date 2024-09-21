<?php

namespace DuncanMcClean\SimpleCommerce\Query\Scopes\Filters;

use DuncanMcClean\SimpleCommerce\Coupons\CouponType as CouponTypeEnum;
use Statamic\Query\Scopes\Filter;

class CouponType extends Filter
{
    public $pinned = true;

    public static function title()
    {
        return __('Type');
    }

    public function fieldItems()
    {
        return [
            'type' => [
                'type' => 'radio',
                'options' => collect(CouponTypeEnum::cases())
                    ->mapWithKeys(fn ($enum) => [$enum->value => CouponTypeEnum::label($enum)])
                    ->all(),
            ],
        ];
    }

    public function apply($query, $values)
    {
        $query->where('type', $values['type']);
    }

    public function badge($values)
    {
        return CouponTypeEnum::label(CouponTypeEnum::from($values['type']));
    }

    public function visibleTo($key)
    {
        return in_array($key, ['coupons']);
    }
}