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

        $orders = Order::with('orderStatus')
            ->orderByDesc('created_at')
            ->paginate(config('statamic.cp.pagination_size'));

        return view('simple-commerce::cp.orders.index', [
            'orders'    => $orders,
            'statuses'  => OrderStatus::all(),
        ]);
    }

    public function edit(Order $order)
    {
        $this->authorize('update', Order::class);

        $crumbs = Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Orders', 'url' => cp_route('orders.index')]]);

        $blueprint = (new Order())->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('simple-commerce::cp.orders.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => array_merge($order->toArray(), [
                'gateway'   => $order->gateway_data['gateway'],
                'paid'      => $order->gateway_data['is_paid'],
            ]),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
            'action'    => cp_route('orders.update', ['order' => $order->uuid]),
        ]);
    }

    public function update(OrderRequest $request, Order $order): Order
    {
        $this->authorize('update', Order::class);

        if ($request->status != $order->status) {
            event(new OrderStatusUpdated($order, $order->customer));
        }

        $order->billingAddress()->updateOrCreate(
            [
                'customer_id'   => $request->customer_id,
                'address1'      => $request->billing_address_1,
                'zip_code'      => $request->billing_zip_code,
            ],
            [
                'name'          => $order->customer->name,
                'address1'      => $request->billing_addresss_1,
                'address2'      => $request->billing_addresss_2,
                'address3'      => $request->billing_addresss_3,
                'city'          => $request->billing_city,
                'zip_code'      => $request->billing_zip_code,
                'country_id'    => Country::where('iso', $request->billing_country)->first()->id,
                'state_id'      => State::where('abbreviation', $request->billing_state)->first()->id ?? null,
            ]
        );

        $order->shippingAddress()->updateOrCreate(
            [
                'customer_id'   => $request->customer_id,
                'address1'      => $request->shipping_address_1,
                'zip_code'      => $request->shipping_zip_code,
            ],
            [
                'name'          => $order->customer->name,
                'address1'      => $request->shipping_addresss_1,
                'address2'      => $request->shipping_addresss_2,
                'address3'      => $request->shipping_addresss_3,
                'city'          => $request->shipping_city,
                'zip_code'      => $request->shipping_zip_code,
                'country_id'    => Country::where('iso', $request->shipping_country)->first()->id,
                'state_id'      => State::where('abbreviation', $request->shipping_state)->first()->id ?? null,
            ]
        );

        $order->update([
            'total'             => $request->total,
            'notes'             => $request->notes,
            'items'             => $request->items,
            'order_status_id'   => $request->order_status_id,
            'currency_id'       => $request->currency_id,
            'customer_id'       => $request->customer_id,
        ]);

        return $order->refresh();
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', Order::class);

        $order->delete();

        return back()->with('success', "Order #{$order->id} has been deleted.");
    }
}
