<?php

namespace DuncanMcClean\SimpleCommerce\Rules;

use Closure;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueCouponValue implements ValidationRule
{
    public function __construct(
        private $except = null,
        private $column = null,
    ) {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->column ??= $attribute;

        $existing = Coupon::query()
            ->when(
                is_array($value),
                fn ($query) => $query->whereIn($this->column, $value),
                fn ($query) => $query->where($this->column, $value)
            )
            ->first();

        if (! $existing) {
            return;
        }

        if ($this->except == $existing->id()) {
            return;
        }

        $fail('simple-commerce::validation.unique_coupon_value')->translate();
    }
}
