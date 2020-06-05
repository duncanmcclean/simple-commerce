<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\OrderRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class OrderController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('view', Order::class);

        $orders = Order::completed()
            ->orderByDesc('created_at')
            ->paginate(config('statamic.cp.pagination_size'));

        if ($request->has('view-carts')) {
            $orders = Order::notCompleted()
                ->orderByDesc('created_at')
                ->paginate(config('statamic.cp.pagination_size'));
        }

        if ($request->has('status')) {
            $status = OrderStatus::where('slug', $request->input('status'))
                ->first();

            $orders = $status
                ->orders()
                ->orderByDesc('created_at')
                ->paginate(config('statamic.cp.pagination_size'));
        }

        return view('simple-commerce::cp.orders.index', [
            'orders'    => $orders,
            'status'    => $status ?? null,
            'statuses'  => OrderStatus::all(),
        ]);
    }

    public function edit(Order $order)
    {
        $this->authorize('update', Order::class);

        $crumbs = Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Orders', 'url' => cp_route('orders.index')]]);

        $values = $order->toArray();

        collect($order->billingAddress)
            ->each(function ($value, $key) use (&$values) {
                $values["billing_{$key}"] = $value;
            });

        collect($order->shippingAddress)
            ->each(function ($value, $key) use (&$values) {
                $values["shipping_{$key}"] = $value;
            });

        $values['line_items'] = [
            'totals' => [
                'items'     => Currency::parse($order->item_total),
                'coupon'    => Currency::parse($order->coupon_total),
                'tax'       => Currency::parse($order->tax_total),
                'shipping'  => Currency::parse($order->shipping_total),
                'overall'   => Currency::parse($order->total),
            ],
            'items' => $order->lineItems()->with('variant', 'variant.product')->get()->map(function ($lineItem) {
                return array_merge($lineItem->toArray(), [
                    'formatted_total' => Currency::parse($lineItem->total),
                ]);
            })->toArray(),
        ];

        $blueprint = (new Order())->blueprint();
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        return view('simple-commerce::cp.orders.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
            'action'    => cp_route('orders.update', ['order' => $order->uuid]),
            'title'     => "Order #{$order->id}",
        ]);
    }

    public function update(OrderRequest $request, Order $order): Order
    {
        $this->authorize('update', Order::class);

        $order->billingAddress()->updateOrCreate(
            [
                'billing_uuid'  => $request->billing_uuid,
                'customer_id'   => $request->customer_id,
            ],
            [
                'name'          => $order->customer->name,
                'address1'      => $request->billing_addresss1,
                'address2'      => $request->billing_addresss2,
                'address3'      => $request->billing_addresss3,
                'city'          => $request->billing_city,
                'zip_code'      => $request->billing_zip_code,
                'country_id'    => Country::where('iso', $request->billing_country)->first()->id,
                'state_id'      => State::where('abbreviation', $request->billing_state)->first()->id ?? null,
            ]
        );

        $order->shippingAddress()->updateOrCreate(
            [
                'shipping_uuid' => $request->billing_uuid,
                'customer_id'   => $request->customer_id,
            ],
            [
                'name'          => $order->customer->name,
                'address1'      => $request->shipping_addresss1,
                'address2'      => $request->shipping_addresss2,
                'address3'      => $request->shipping_addresss3,
                'city'          => $request->shipping_city,
                'zip_code'      => $request->shipping_zip_code,
                'country_id'    => Country::where('iso', $request->shipping_country)->first()->id,
                'state_id'      => State::where('abbreviation', $request->shipping_state)->first()->id ?? null,
            ]
        );

        $order->update([
            'item_total'        => $request->item_total,
            'tax_total'         => $request->tax_total,
            'shipping_total'    => $request->shipping_total,
            'total'             => $request->total,

            'customer_id'       => $request->customer_id,
            'order_status_id'   => $request->order_status_id,
            'currency_id'       => $request->currency_id,
            'is_paid'           => $request->is_paid,
            'is_completed'      => $request->is_completed,
        ]);

        if ($request->order_status_id != $order->order_status_id) {
            event(new OrderStatusUpdated($order, $order->customer));
        }

        return $order->refresh();
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', Order::class);

        $order->delete();
    }
}
