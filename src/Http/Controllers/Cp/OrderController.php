<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\OrderRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class OrderController extends CpController
{
    public function index()
    {
        $this->authorize('view', Order::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce'],
        ]);

        $orders = Order::with('orderStatus')
            ->orderByDesc('created_at')
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
            ['text' => 'Simple Commerce'],
            ['text' => 'Orders', 'url' => cp_route('orders.index')],
        ]);

        $blueprint = Blueprint::find('simple-commerce/order');

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

    public function update(OrderRequest $request, Order $order): Order
    {
        $this->authorize('update', Order::class);

        if ($request->status != $order->status) {
            event(new OrderStatusUpdated($order, $order->customer));
        }

        $billingAddress = new Address();
        $billingAddress->name = $order->customer->name;
        $billingAddress->address1 = $request->billing_address_1;
        $billingAddress->address2 = $request->billing_address_2;
        $billingAddress->address3 = $request->billing_address_3;
        $billingAddress->city = $request->billing_city;
        $billingAddress->zip_code = $request->billing_zip_code;
        $billingAddress->country_id = Country::where('iso', $request->billing_country)->first()->id;
        $billingAddress->state_id = State::where('abbreviation', $request->billing_state)->first()->id ?? null;
        $billingAddress->customer_id = $order->customer_id;
        $billingAddress->save();

        $shippingAddress = new Address();
        $shippingAddress->name = $order->customer->name;
        $shippingAddress->address1 = $request->shipping_address_1;
        $shippingAddress->address2 = $request->shipping_address_2;
        $shippingAddress->address3 = $request->shipping_address_3;
        $shippingAddress->city = $request->shipping_city;
        $shippingAddress->zip_code = $request->shipping_zip_code;
        $shippingAddress->country_id = Country::where('iso', $request->shipping_country)->first()->id;
        $shippingAddress->state_id = State::where('abbreviation', $request->shipping_state)->first()->id ?? null;
        $shippingAddress->customer_id = $order->customer_id;
        $shippingAddress->save();

        $order->total = $request->total;
        $order->notes = $request->notes;
        $order->items = $request->items;
        $order->order_status_id = $request->order_status_id;
        $order->currency_id = $request->currency_id;
        $order->customer_id = $request->customer_id;
        $order->billing_address_id = $billingAddress->id;
        $order->shipping_address_id = $shippingAddress->id;
        $order->save();

        return $order;
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', Order::class);

        $order->delete();

        return back()
            ->with('success', 'Order has been deleted.');
    }
}
