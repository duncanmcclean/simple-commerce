<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\Variables\VariableFieldtype;
use DoubleThreeDigital\SimpleCommerce\Orders\StatusLogEvent;
use Illuminate\Support\Arr;

class StatusLogFieldtype extends VariableFieldtype
{
    protected static $handle = 'sc_status_log';

    public static function title()
    {
        return __('Simple Commerce: Status Log');
    }

    public function augment($value)
    {
        // Support the old format for the status log. We can remove this in the future.
        if (! empty($value) && ! is_array(Arr::first($value))) {
            return collect($value)->map(function ($timestamp, $status) {
                return Carbon::parse($timestamp);
            })->toArray();
        }

        return collect($value)->map(function (array $statusLogEvent) {
            return new StatusLogEvent(
                $statusLogEvent['status'],
                $statusLogEvent['timestamp'],
                $statusLogEvent['data'] ?? []
            );
        });
    }

    public function toQueryableValue($value)
    {
        return $this->augment($value);
    }
}
