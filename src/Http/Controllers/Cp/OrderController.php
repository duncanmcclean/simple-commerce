<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Events\OrderStatusUpdated;
use Damcclean\Commerce\Facades\Order;
use Damcclean\Commerce\Http\Requests\OrderStoreRequest;
use Damcclean\Commerce\Http\Requests\OrderUpdateRequest;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class OrderController extends CpController
{
    public function index()
    {
        return view('commerce::cp.orders.index', [
            'orders' => Order::all(),
        ]);
    }

    public function create()
    {
        $blueprint = Blueprint::find('order');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.orders.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
        ]);
    }

    public function store(OrderStoreRequest $request)
    {
        $validated = $request->validated();

        $order = Order::save($request->all());

        return ['redirect' => cp_route('orders.edit', ['order' => $order->data['id']])];
    }

    public function edit($order)
    {
        $order = Order::find($order);

        $blueprint = Blueprint::find('order');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.orders.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $order,
            'meta'      => $fields->meta(),
        ]);
    }

    public function update(OrderUpdateRequest $request, $order)
    {
        $validated = $request->validated();

        $order = Order::find($order)->toArray();

        if ($request->status != $order['status']) {
            event(new OrderStatusUpdated($order));
        }

        return Order::update($order['id'], $request->all());
    }

    public function destroy($order)
    {
        $order = Order::delete(Order::find($order)['slug']);

        return redirect(cp_route('orders.index'));
    }
}
