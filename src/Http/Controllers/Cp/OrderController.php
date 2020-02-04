<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\OrderStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\OrderUpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class OrderController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
        ]);

        $orders = Order::with('orderStatus')
            ->paginate(config('statamic.cp.pagination_size'));

        return view('commerce::cp.orders.index', [
            'crumbs' => $crumbs,
            'orders' => $orders,
            'statuses' => OrderStatus::all(),
        ]);
    }

    public function edit(Order $order)
    {
        $this->authorize('update', Order::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Orders', 'url' => cp_route('orders.index')],
        ]);

        $blueprint = Blueprint::find('order');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.orders.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $order,
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function update(OrderUpdateRequest $request, Order $order)
    {
        $this->authorize('update', Order::class);

        $validated = $request->validated();

        $order = Order::find($order)->first();

        if ($request->status != $order->status) {
            event(new OrderStatusUpdated($order));
        }

        // TODO: actually update the entry

        return $order;
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', Order::class);

        $order->delete();

        return back()->with('success', 'Order has been deleted.');
    }
}
