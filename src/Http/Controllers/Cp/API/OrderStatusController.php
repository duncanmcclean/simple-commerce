<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\OrderStatusDeleteRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\OrderStatusRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
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
        return OrderStatus::create([
            'name'          => $request->name,
            'slug'          => $request->slug,
            'description'   => $request->description,
            'color'         => $request->color,
            'primary'       => false,
        ]);
    }

    public function update(OrderStatus $status, OrderStatusRequest $request): OrderStatus
    {
        if ($request->primary === true) {
            OrderStatus::where('primary', true)->first()->update([
                'primary' => false,
            ]);
        }

        $status->update([
            'name'          => $request->name,
            'slug'          => $request->slug,
            'description'   => $request->description,
            'color'         => $request->color,
            'primary'       => $request->primary,
        ]);

        return $status->refresh();
    }

    public function destroy(OrderStatus $status, OrderStatusDeleteRequest $request)
    {
        if (OrderStatus::all()->count() === 1) {
            return redirect(cp_route('settings.edit'))
                ->with('error', "You can't delete the only order status.");
        }

        if ($status->primary === true) {
            return redirect(cp_route('settings.edit'))
                ->with('error', "You can't delete the primary order status.");
        }

        $status->orders()->update([
           'order_status_id' => $request->assign,
        ]);

        $status->delete();
    }
}
