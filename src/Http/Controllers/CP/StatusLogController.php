<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\StatusLogEvent;
use DuncanMcClean\SimpleCommerce\Support\Runway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Facades\User;

class StatusLogController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', fn ($attribute, $value, $fail) => Order::find($value) ? true : $fail('Order not found.')],
        ]);

        $order = Order::find($validated['order_id']);
        $orderResource = $order->resource();

        $authorized = $orderResource instanceof Model
            ? User::current()->can('view', [Runway::orderModel(), $orderResource])
            : User::current()->can('view', $orderResource);

        if (! $authorized) {
            abort(403);
        }

        return $order->statusLog()
            ->reverse()
            ->groupBy(function (StatusLogEvent $statusLogEvent) {
                return $statusLogEvent->date()->clone()->startOfDay()->format('U');
            })
            ->map(function ($events, $day) {
                return [
                    'day' => $day,
                    'events' => $events->map(function (StatusLogEvent $statusLogEvent) {
                        $user = null;

                        if (Arr::has($statusLogEvent->data, 'user')) {
                            $user = User::find($statusLogEvent->data['user']);

                            $user = [
                                'id' => $user->id(),
                                'email' => $user->email(),
                                'name' => $user->name(),
                                'avatar' => $user->avatar(),
                                'initials' => $user->initials(),
                            ];
                        }

                        return array_merge($statusLogEvent->toArray(), ['user' => $user]);
                    }),
                ];
            })
            ->reverse()
            ->values();
    }
}
