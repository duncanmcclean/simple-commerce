<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Events\OrderStatusUpdated;
use Damcclean\Commerce\Models\Address;
use Damcclean\Commerce\Models\Order;
use Damcclean\Commerce\Http\Requests\OrderStoreRequest;
use Damcclean\Commerce\Http\Requests\OrderUpdateRequest;
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
            'createUrl' => (new Order())->createUrl(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Order::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Orders', 'url' => cp_route('orders.index')],
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
        $this->authorize('create', Order::class);

        $validated = $request->validated();

        $order = new Order();
        $order->uid = (new Stache())->generateId();
        $order->total = $request->total;
        $order->notes = $request->notes;
        $order->products = $request->products;
        $order->status_id = $request->status; // TODO: implement status fieldtype
        $order->customer_id = $request->customer[0];
        $order->save();

        $billingAddress = new Address();
        $billingAddress->uid = (new Stache())->generateId();
        $billingAddress->country_id = $request->billing_country[0];
        $billingAddress->state_id = $request->billing_state ?? null;
        $billingAddress->name = $request->name;
        $billingAddress->address1 = $request->billing_address_1;
        $billingAddress->address2 = $request->billing_address_2;
        $billingAddress->address3 = $request->billing_address_3;
        $billingAddress->city = $request->billing_city;
        $billingAddress->zip_code = $request->billing_zip_code;
        $billingAddress->customer_id = $order->customer_id;
        $billingAddress->save();

        $shippingAddress = new Address();
        $shippingAddress->uid = (new Stache())->generateId();
        $shippingAddress->country_id = $request->shipping_country[0];
        $shippingAddress->state_id = $request->shipping_state ?? null;
        $shippingAddress->name = $request->name;
        $shippingAddress->address1 = $request->shipping_address_1;
        $shippingAddress->address2 = $request->shipping_address_2;
        $shippingAddress->address3 = $request->shipping_address_3;
        $shippingAddress->city = $request->shipping_city;
        $shippingAddress->zip_code = $request->shipping_zip_code;
        $shippingAddress->customer_id = $order->customer_id;
        $shippingAddress->save();

        $order->billing_address_id = $billingAddress->id;
        $order->shipping_address_id = $shippingAddress->id;
        $order->save();

        return ['redirect' => cp_route('orders.edit', ['order' => $order->uid])];
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

        return redirect(cp_route('orders.index'));
    }
}
