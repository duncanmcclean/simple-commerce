<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Order;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class OrderController extends CpController
{
    public function index()
    {
        return view('commerce::cp.orders.index', [
            'orders' => Order::all()
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

    public function store(Request $request)
    {
        $validated = []; // WIP

        $order = Order::save($request->all());

        return ['redirect' => cp_route('orders.edit', ['order' => $order['id']])];
    }

    public function edit($order)
    {
        $order = Order::findBySlug($order);

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

    public function update(Request $request, $order)
    {
        $validated = []; // wip

        return Order::update($order, $request->all());
    }

    public function destroy($order)
    {
        $coupon = Order::delete($order);

        return redirect(cp_route('orders.index'));
    }
}
