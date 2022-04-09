<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as CouponContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as CustomerContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\OrderRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\OrderNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Collection;
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
        return Entry::whereCollection($this->collection)->all();
    }

    public function find($id): ?Order
    {
        $entry = Entry::find($id);

        if (! $entry) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        $order = app(Order::class)
            ->resource($entry)
            ->id($entry->id())
            ->orderNumber($entry->get('order_number') ?? str_replace('#', '', $entry->get('title')))
            ->isPaid($entry->get('is_paid') ?? false)
            ->isShipped($entry->get('is_shipped') ?? false)
            ->isRefunded($entry->get('is_refunded') ?? false)
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

        if ($entry->has('gateway')) {
            $order->gateway($entry->get('gateway'));
        }

        return $order->data(array_merge(
            Arr::except(
                $entry->data()->toArray(),
                ['is_paid', 'is_shipped', 'is_refunded', 'items', 'grand_total', 'items_total', 'tax_total', 'shipping_total', 'coupon_total', 'customer', 'coupon', 'gateway']
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

    public function save($order): void
    {
        $entry = $order->resource();

        if (! $entry) {
            $entry = Entry::make()
                ->id(Stache::generateId())
                ->collection($this->collection);
        }

        if ($order->get('site')) {
            $entry->site($order->get('site'));
        }

        if ($order->get('slug')) {
            $entry->slug($order->get('slug'));
        }

        $entry->published($order->get('published', false));

        $entry->data(
            array_merge(
                $order->data()->except(['id', 'site', 'slug'])->toArray(),
                [
                    'order_number' => $this->generateOrderNumber(),
                    'is_paid' => $order->isPaid(),
                    'is_shipped' => $order->isShipped(),
                    'is_refunded' => $order->isRefunded(),
                    'items' => $order->lineItems()->toArray(),
                    'grand_total' => $order->grandTotal(),
                    'items_total' => $order->itemsTotal(),
                    'tax_total' => $order->taxTotal(),
                    'shipping_total' => $order->shippingTotal(),
                    'coupon_total' => $order->couponTotal(),
                    'customer' => $order->customer() instanceof CustomerContract ? $order->customer()->id() : $order->customer(),
                    'coupon' => $order->coupon() instanceof CouponContract ? $order->coupon()->id() : $order->coupon(),
                    'gateway' => $order->gateway(),
                ],
            )
        );

        $entry->save();

        $order->id = $entry->id();
        $order->orderNumber = $entry->get('order_number');
        $order->isPaid = $entry->get('is_paid');
        $order->isShipped = $entry->get('is_shipped');
        $order->isRefunded = $entry->get('is_refunded');
        $order->lineItems = collect($entry->get('items'));
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

    public function delete($order): void
    {
        $order->resource()->delete();
    }

    protected function isUsingEloquentDriverWithIncrementingIds(): bool
    {
        return config('statamic.eloquent-driver.entries.model') === \Statamic\Eloquent\Entries\EntryModel::class;
    }

    protected function generateOrderNumber(): int
    {
        $lastOrder = Collection::find($this->collection)
            ->queryEntries()
            ->orderBy('order_number', 'desc')
            ->where('order_number', '!=', null)
            ->first();

        // Fallback to get order number from title (otherwise: start from the start..)
        if (! $lastOrder) {
            $lastOrder = Collection::find($this->collection)
                ->queryEntries()
                ->orderBy('title', 'desc')
                ->where('title', '!=', null)
                ->first();

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
