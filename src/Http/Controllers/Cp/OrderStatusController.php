<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class OrderStatusController extends CpController
{
    public function index()
    {
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

        return OrderStatus::all();
    }

    public function store(Request $request)
    {
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

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
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

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
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

        // TODO: make sure that the user cant delete the only remaining order status
        // TODO: do something with the orders that are currently using this status

        $status->delete();

        return redirect(cp_route('settings.edit'))
            ->with('success', 'Deleted order status');
    }
}
