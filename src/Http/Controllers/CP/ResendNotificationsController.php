<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\PaymentStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Listeners\SendConfiguredNotifications;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use Illuminate\Http\Request;

class ResendNotificationsController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'status' => ['required'],
            'order_id' => ['required', fn ($attribute, $value, $fail) => Order::find($value) ? true : $fail('Order not found.')],
        ]);

        $order = Order::find($validated['order_id']);
        $status = $this->getStatusFromValue($validated['status']);

        if ($status instanceof OrderStatus) {
            $event = new OrderStatusUpdated($order, $status);
        }

        if ($status instanceof PaymentStatus) {
            $event = new PaymentStatusUpdated($order, $status);
        }

        (new SendConfiguredNotifications())->handle($event);

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
