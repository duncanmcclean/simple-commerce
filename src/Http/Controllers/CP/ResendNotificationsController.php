<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Events\OrderStatusUpdated;
use DuncanMcClean\SimpleCommerce\Events\PaymentStatusUpdated;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Listeners\SendConfiguredNotifications;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Support\Runway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Statamic\Facades\User;

class ResendNotificationsController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'status' => ['required'],
            'order_id' => ['required', fn ($attribute, $value, $fail) => Order::find($value) ? true : $fail('Order not found.')],
        ]);

        $order = Order::find($validated['order_id']);
        $orderResource = $order->resource();

        $authorized = $orderResource instanceof Model
            ? User::current()->can('edit', [Runway::orderModel(), $orderResource])
            : User::current()->can('edit', $orderResource);

        if (! $authorized) {
            abort(403);
        }

        $status = $this->getStatusFromValue($validated['status']);

        if ($status instanceof OrderStatus) {
            $event = new OrderStatusUpdated($order, $status);
        }

        if ($status instanceof PaymentStatus) {
            $event = new PaymentStatusUpdated($order, $status);
        }

        (new SendConfiguredNotifications)->handle($event);

        return response()->json();
    }

    // TODO: maybe refactor this, it's not pretty
    private function getStatusFromValue(string $value): OrderStatus|PaymentStatus
    {
        try {
            return OrderStatus::from($value);
        } catch (\Throwable $th) {
            return PaymentStatus::from($value);
        }
    }
}
