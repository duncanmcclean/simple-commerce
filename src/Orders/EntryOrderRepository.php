<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Contracts\Coupon as CouponContract;
use DuncanMcClean\SimpleCommerce\Contracts\Customer as CustomerContract;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\OrderRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Exceptions\OrderNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class EntryOrderRepository implements RepositoryContract
{
    protected $collection;

    public function __construct()
    {
        $this->collection = SimpleCommerce::orderDriver()['collection'];
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(EntryQueryBuilder::class, [
            'store' => app('stache')->store('entries'),
        ])->where('collection', $this->collection);
    }

    public function find($id): ?Order
    {
        $entry = Entry::find($id);

        if (! $entry) {
            return null;
        }

        return $this->fromEntry($entry);
    }

    public function findOrFail($id): Order
    {
        $order = $this->find($id);

        if (! $order) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        return $order;
    }

    public function fromEntry($entry): Order
    {
        $order = app(Order::class)
            ->resource($entry)
            ->id($entry->id())
            ->orderNumber($entry->get('order_number') ?? str_replace('#', '', $entry->get('title')))
            ->status($entry->get('order_status') ?? 'cart')
            ->paymentStatus($entry->get('payment_status') ?? 'unpaid')
            ->lineItems($entry->get('items') ?? [])
            ->grandTotal($entry->get('grand_total') ?? 0)
            ->itemsTotal($entry->get('items_total') ?? 0)
            ->taxTotal($entry->get('tax_total') ?? 0)
            ->shippingTotal($entry->get('shipping_total') ?? 0)
            ->couponTotal($entry->get('coupon_total') ?? 0);

        if ($entry->has('customer')) {
            $order->customer($entry->get('customer'));
        }

        if ($entry->has('coupon')) {
            $order->coupon($entry->get('coupon'));
        }

        if ($gatewayData = $entry->get('gateway')) {
            $order->gatewayData(
                gateway: $gatewayData['use'] ?? null,
                data: $gatewayData['data'] ?? null,
                refund: $gatewayData['refund'] ?? null,
            );
        }

        return $order->data(array_merge(
            Arr::except(
                $entry->data()->toArray(),
                ['order_status', 'payment_status', 'items', 'grand_total', 'items_total', 'tax_total', 'shipping_total', 'coupon_total', 'customer', 'coupon', 'gateway']
            ),
            [
                'site' => optional($entry->site())->handle(),
                'slug' => $entry->slug(),
                'published' => $entry->published(),
            ]
        ));
    }

    public function make(): Order
    {
        return app(Order::class);
    }

    public function save(Order $order): void
    {
        $entry = $order->resource();

        if (! $entry) {
            $entry = Entry::make()
                ->id($order->id() ?? Stache::generateId())
                ->collection($this->collection);
        }

        if ($order->get('site')) {
            $entry->locale($order->get('site'));
        }

        if ($order->get('slug')) {
            $entry->slug($order->get('slug'));
        }

        $entry->published($order->get('published', true));

        $entry->data(
            array_merge(
                $order->data()->except(['id', 'site', 'slug'])->toArray(),
                [
                    'order_number' => $order->has('order_number') ? $order->get('order_number') : $this->generateOrderNumber(),
                    'order_status' => $order->status()->value,
                    'payment_status' => $order->paymentStatus()->value,
                    'items' => $order->lineItems()->map->toArray()->toArray(),
                    'grand_total' => $order->grandTotal(),
                    'items_total' => $order->itemsTotal(),
                    'tax_total' => $order->taxTotal(),
                    'shipping_total' => $order->shippingTotal(),
                    'coupon_total' => $order->couponTotal(),
                    'customer' => $order->customer() instanceof CustomerContract ? $order->customer()->id() : $order->customer(),
                    'coupon' => $order->coupon() instanceof CouponContract ? $order->coupon()->id() : $order->coupon(),
                    'gateway' => $order->gatewayData()?->toArray(),
                ],
            )
        );

        $entry->save();

        $order->id = $entry->id();
        $order->orderNumber = $entry->get('order_number');
        $order->status = OrderStatus::from($entry->get('order_status'));
        $order->paymentStatus = PaymentStatus::from($entry->get('payment_status'));
        // $order->lineItems = collect($entry->get('items'));
        $order->grandTotal = $entry->get('grand_total');
        $order->itemsTotal = $entry->get('items_total');
        $order->taxTotal = $entry->get('tax_total');
        $order->shippingTotal = $entry->get('shipping_total');
        $order->couponTotal = $entry->get('coupon_total');
        $order->customer = $entry->get('customer') !== null
            ? Customer::find($entry->get('customer'))
            : null;
        $order->coupon = $entry->get('coupon') !== null
            ? Coupon::find($entry->get('coupon'))
            : null;
        $order->gateway = $entry->get('gateway');
        $order->data = $entry->data();
        $order->resource = $entry;
    }

    public function delete(Order $order): void
    {
        $order->resource()->delete();
    }

    protected function isUsingEloquentDriverWithIncrementingIds(): bool
    {
        return config('statamic.eloquent-driver.entries.model') === \Statamic\Eloquent\Entries\EntryModel::class;
    }

    protected function generateOrderNumber(): int
    {
        $lastOrder = $this->query()->where('order_number', '!=', null)->orderByDesc('order_number')->first();

        // Fallback to get order number from title (otherwise: start from the start..)
        if (! $lastOrder) {
            $lastOrder = $this->query()->where('title', '!=', null)->orderByDesc('title')->first();

            // And if we don't have any orders with the old title format, start from the start.
            if (! $lastOrder) {
                return config('simple-commerce.minimum_order_number', 1000);
            }

            $lastOrderNumber = (int) Str::of($lastOrder->get('title'))
                ->replace('Order ', '')
                ->replace('#', '')
                ->__toString();
        } else {
            $lastOrderNumber = $lastOrder->get('order_number');
        }

        return (int) $lastOrderNumber + 1;
    }

    public static function bindings(): array
    {
        return [];
    }
}
