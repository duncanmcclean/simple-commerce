<?php

namespace DoubleThreeDigital\SimpleCommerce\Models\Traits;

use DoubleThreeDigital\SimpleCommerce\Models\Attribute;

trait HasAttributes
{
    public function attributes()
    {
        return $this->morphMany(Attribute::class, 'attributable');
    }
}
