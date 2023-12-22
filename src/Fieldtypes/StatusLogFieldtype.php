<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\StatusLogEvent;
use Illuminate\Support\Arr;
use Statamic\Fields\Fieldtype;

class StatusLogFieldtype extends Fieldtype
{
    protected static $handle = 'sc_status_log';

    protected $categories = ['special'];

    protected $selectable = false;

    public static function title()
    {
        return __('Simple Commerce: Status Log');
    }

    public function preload()
    {
        return [
            'indexUrl' => cp_route('simple-commerce.fieldtype-api.status-log'),
            'resendNotificationsUrl' => cp_route('simple-commerce.resend-notifications'),
            'orderStatuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ];
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
