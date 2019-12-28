<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Events\OrderStatusUpdated;
use Damcclean\Commerce\Facades\Order;
use Damcclean\Commerce\Http\Requests\OrderStoreRequest;
use Damcclean\Commerce\Http\Requests\OrderUpdateRequest;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class OrderController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '/commerce'],
        ]);

        $orders = Order::all()
            ->map(function ($order) {
                return array_merge($order->toArray(), [
                    'edit_url' => cp_route('orders.edit', ['order' => $order['id']]),
                    'delete_url' => cp_route('orders.destroy', ['order' => $order['id']]),
                ]);
            });

        return view('commerce::cp.orders.index', [
            'orders' => $orders,
            'crumbs' => $crumbs,
        ]);
    }

    public function create()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '/commerce'],
            ['text' => 'Orders', 'url' => '/orders'],
        ]);

        $blueprint = Blueprint::find('order');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.orders.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
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
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '/commerce'],
            ['text' => 'Orders', 'url' => '/orders'],
        ]);

        $order = Order::find($order);

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
