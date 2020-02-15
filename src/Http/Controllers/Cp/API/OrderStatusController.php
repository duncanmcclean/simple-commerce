<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\OrderStatusRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Http\Controllers\CP\CpController;

class OrderStatusController extends CpController
{
    public function index(): Collection
    {
        return OrderStatus::all();
    }

    public function store(OrderStatusRequest $request): OrderStatus
    {
        $status = new OrderStatus();
        $status->name = $request->name;
        $status->slug = $request->slug;
        $status->description = $request->description;
        $status->color = $request->color;
        $status->primary = false;
        $status->save();

        return $status;
    }

    public function update(OrderStatus $status, OrderStatusRequest $request): OrderStatus
    {
        if ($request->primary === true) {
            $currentPrimary = OrderStatus::where('primary', true)->first();
            $currentPrimary->primary = false;
            $currentPrimary->save();
        }

        $status->name = $request->name;
        $status->slug = $request->slug;
        $status->description = $request->description;
        $status->color = $request->color;
        $status->primary = $request->primary;
        $status->save();

        return $status;
    }

    public function destroy(OrderStatus $status, Request $request)
    {
        collect(Order::where('order_status_id', $status->id)->get())
            ->each(function ($order) use ($request) {
                $order->order_status_id = $request->assign;
                $order->save();
            });

        if (OrderStatus::all()->count() === 1) {
            return redirect(cp_route('settings.edit'))
                ->with('error', "You can't delete the only order status.");
        }

        if ($status->primary === true) {
            return redirect(cp_route('settings.edit'))
                ->with('error', "You can't delete the primary order status.");
        }

        $status->delete();
    }
}
