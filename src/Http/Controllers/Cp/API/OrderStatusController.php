<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class OrderStatusController extends CpController
{
    public function index()
    {
        return OrderStatus::all();
    }

    public function store(Request $request)
    {
        // TODO: use a validation request here

        $status = new OrderStatus();
        $status->uuid = (new Stache())->generateId();
        $status->name = $request->name;
        $status->slug = $request->slug;
        $status->description = $request->description;
        $status->color = $request->color;
        $status->primary = false;
        $status->save();

        return $status;
    }

    public function update(OrderStatus $status, Request $request)
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

    public function destroy(OrderStatus $status)
    {
        // TODO: do something with the orders that are currently using this status

        if (OrderStatus::all()->count() === 1) {
            return redirect(cp_route('settings.edit'))
                ->with('error', "You can't delete the only order status.");
        }

        if ($status->primary === true) {
            return redirect(cp_route('settings.edit'))
                ->with('error', "You can't delete the primary order status.");
        }

        $status->delete();

        return redirect(cp_route('settings.edit'))
            ->with('success', 'Deleted order status');
    }
}
