<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\Variables\VariableFieldtype;

class StatusLogFieldtype extends VariableFieldtype
{
    protected static $handle = 'sc_status_log';

    public static function title()
    {
        return __('Simple Commerce: Status Log');
    }

    public function augment($value)
    {
        return collect($value)->map(function ($timestamp, $status) {
            return Carbon::parse($timestamp);
        })->toArray();
    }

    public function toQueryableValue($value)
    {
        return $this->augment($value);
    }
}
