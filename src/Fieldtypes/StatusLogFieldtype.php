<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use Carbon\Carbon;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Orders\StatusLogEvent;
use DuncanMcClean\SimpleCommerce\Orders\StatusLogModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        // When orders are stored in the database, the status log is stored as a HasMany relationship.
        if ($value instanceof HasMany) {
            $value = $value->get();

            $value = collect($value)->map(function (StatusLogModel $statusLogModel) {
                return ['status' => $statusLogModel->status, 'timestamp' => $statusLogModel->timestamp, 'data' => $statusLogModel->data ?? []];
            });
        }

        // Support the old format for the status log. We can remove this in the future.
        if (! empty($value) && ! is_array(Arr::first($value))) {
            $value = collect($value)->map(function ($date, $status) {
                return [
                    'status' => $status,
                    'timestamp' => Carbon::parse($date)->timestamp,
                    'data' => [],
                ];
            })->toArray();
        }

        return collect($value)->map(function (array $statusLogEvent) {
            // When orders are stored in the database, the timestamp is cast to a Carbon instance.
            // However, to pass it into the StatusLogEvent class, we need to convert it back to a timestamp.
            $timestamp = $statusLogEvent['timestamp'] instanceof Carbon
                ? $statusLogEvent['timestamp']->timestamp
                : $statusLogEvent['timestamp'];

            return new StatusLogEvent(
                $statusLogEvent['status'],
                $timestamp,
                $statusLogEvent['data'] ?? []
            );
        });
    }

    /**
     * Allows for querying the timestamp of a status (it'll query the latest timestamp for that status)
     * Eg: `->whereDate('status_log->paid', '>', '2024-01-01')`
     */
    public function toQueryableValue($value)
    {
        return $this->augment($value)
            ->groupBy(fn (StatusLogEvent $statusLogEvent) => $statusLogEvent->status->value)
            ->map(function ($events) {
                $latestEvent = $events->sortByDesc(fn (StatusLogEvent $statusLogEvent) => $statusLogEvent->date())->first();

                return $latestEvent->date();
            })
            ->toArray();
    }
}
