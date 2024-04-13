<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Doctrine\DBAL\Schema\Column;
use DuncanMcClean\SimpleCommerce\Contracts\Coupon as CouponContract;
use DuncanMcClean\SimpleCommerce\Contracts\Customer as CustomerContract;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\OrderRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Exceptions\OrderNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Schema;

class EloquentOrderRepository implements RepositoryContract
{
    protected $model;

    protected $knownColumns = [
        'id', 'order_number', 'order_status', 'payment_status', 'items', 'grand_total', 'items_total', 'tax_total',
        'shipping_total', 'coupon_total', 'shipping_name', 'shipping_address', 'shipping_address_line2', 'shipping_city',
        'shipping_postal_code', 'shipping_region', 'shipping_country', 'billing_name', 'billing_address', 'billing_address_line2',
        'billing_city', 'billing_postal_code', 'billing_region', 'billing_country', 'use_shipping_address_for_billing', 'customer_id',
        'coupon', 'gateway', 'data', 'created_at', 'updated_at',
    ];

    public function __construct()
    {
        $this->model = SimpleCommerce::orderDriver()['model'];
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(EloquentQueryBuilder::class, [
            'builder' => (new $this->model)->query(),
        ]);
    }

    public function find($id): ?Order
    {
        $model = (new $this->model)->find($id);

        if (! $model) {
            return null;
        }

        return $this->fromModel($model);
    }

    public function findOrFail($id): Order
    {
        $order = $this->find($id);

        if (! $order) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        return $order;
    }

    public function fromModel(OrderModel $model)
    {
        $order = app(Order::class)
            ->resource($model)
            ->id($model->id)
            ->orderNumber($model->order_number)
            ->status($model->order_status ?? 'cart')
            ->paymentStatus($model->payment_status ?? 'unpaid')
            ->lineItems($model->items)
            ->grandTotal($model->grand_total)
            ->itemsTotal($model->items_total)
            ->taxTotal($model->tax_total)
            ->shippingTotal($model->shipping_total)
            ->couponTotal($model->coupon_total)
            ->customer($model->customer_id)
            ->coupon($model->coupon)
            ->data(
                collect($model->data)
                    ->merge([
                        'shipping_name' => $model->shipping_name,
                        'shipping_address' => $model->shipping_address,
                        'shipping_address_line2' => $model->shipping_address_line2,
                        'shipping_city' => $model->shipping_city,
                        'shipping_postal_code' => $model->shipping_postal_code,
                        'shipping_region' => $model->shipping_region,
                        'shipping_country' => $model->shipping_country,
                        'billing_name' => $model->billing_name,
                        'billing_address' => $model->billing_address,
                        'billing_address_line2' => $model->billing_address_line2,
                        'billing_city' => $model->billing_city,
                        'billing_postal_code' => $model->billing_postal_code,
                        'billing_region' => $model->billing_region,
                        'billing_country' => $model->billing_country,
                        'use_shipping_address_for_billing' => $model->use_shipping_address_for_billing,
                    ])
                    ->merge(
                        collect($this->getCustomColumns())
                            ->mapWithKeys(function ($columnName) use ($model) {
                                return [$columnName => $model->{$columnName}];
                            })
                            ->toArray()
                    )
                    ->merge([
                        'status_log' => $model->statusLog()->get()->map(fn ($statusLog) => [
                            'status' => $statusLog->status,
                            'timestamp' => $statusLog->timestamp->timestamp,
                            'data' => $statusLog->data ?? [],
                        ]),
                    ])
            );

        if ($model->gateway) {
            $order->gatewayData(
                gateway: $model->gateway['use'] ?? null,
                data: $model->gateway['data'] ?? null,
                refund: $model->gateway['refund'] ?? null
            );
        }

        return $order;
    }

    public function make(): Order
    {
        return app(Order::class);
    }

    public function save($order): void
    {
        $model = $order->resource();

        if (! $model) {
            $model = new $this->model();
        }

        $model->order_number = $order->orderNumber() ?? $this->generateOrderNumber();
        $model->order_status = $order->status()->value;
        $model->payment_status = $order->paymentStatus()->value;
        $model->items = $order->lineItems()->map->toArray();
        $model->grand_total = $order->grandTotal();
        $model->items_total = $order->itemsTotal();
        $model->tax_total = $order->taxTotal();
        $model->shipping_total = $order->shippingTotal();
        $model->coupon_total = $order->couponTotal();
        $model->customer_id = $order->customer() instanceof CustomerContract ? $order->customer()->id() : $order->customer();
        $model->coupon = $order->coupon() instanceof CouponContract ? $order->coupon()->id() : $order->coupon();
        $model->gateway = $order->gatewayData()?->toArray();

        $model->shipping_name = $order->get('shipping_name');
        $model->shipping_address = $order->get('shipping_address');
        $model->shipping_address_line2 = $order->get('shipping_address_line2');
        $model->shipping_city = $order->get('shipping_city');
        $model->shipping_postal_code = $order->get('shipping_postal_code');
        $model->shipping_region = $order->get('shipping_region');
        $model->shipping_country = $order->get('shipping_country');

        $model->billing_name = $order->get('billing_name');
        $model->billing_address = $order->get('billing_address');
        $model->billing_address_line2 = $order->get('billing_address_line2');
        $model->billing_city = $order->get('billing_city');
        $model->billing_postal_code = $order->get('billing_postal_code');
        $model->billing_region = $order->get('billing_region');
        $model->billing_country = $order->get('billing_country');

        $model->use_shipping_address_for_billing = $order->get('use_shipping_address_for_billing') == 'true';

        // If anything in the order data has it's own column, save it
        // there, rather than in the data column.
        collect($this->getCustomColumns())
            ->filter(function ($columnName) use ($order) {
                return $order->has($columnName);
            })
            ->each(function ($columnName) use (&$model, $order) {
                $model->{$columnName} = $order->get($columnName);
            });

        // Set the value of the data column - we take out any 'known' columns,
        // along with any custom columns.
        $model->data = $order->data()
            ->except($this->knownColumns)
            ->except($this->getCustomColumns());

        $model->save();

        // Loop through status log events & create/update them in the database.
        $order->statusLog()->map(function (StatusLogEvent $statusLogEvent) use ($model) {
            StatusLogModel::updateOrCreate(
                ['order_id' => $model->id, 'status' => $statusLogEvent->status, 'timestamp' => $statusLogEvent->date()],
                ['data' => $statusLogEvent->data ?? []]
            );
        });

        $order->id = $model->id;
        $order->orderNumber = $model->order_number;
        $order->status = OrderStatus::from($model->order_status);
        $order->paymentStatus = PaymentStatus::from($model->payment_status);
        // $order->lineItems = collect($model->items);
        $order->grandTotal = $model->grand_total;
        $order->itemsTotal = $model->items_total;
        $order->taxTotal = $model->tax_total;
        $order->shippingTotal = $model->shipping_total;
        $order->couponTotal = $model->coupon_total;
        $order->customer = $model->customer_id ? Customer::find($model->customer_id) : null;
        $order->coupon = $model->coupon ? Coupon::find($model->coupon) : null;
        $order->gateway = $model->gateway;

        $order->data = collect($model->data)
            ->merge([
                'shipping_name' => $model->shipping_name,
                'shipping_address' => $model->shipping_address,
                'shipping_address_line2' => $model->shipping_address_line2,
                'shipping_city' => $model->shipping_city,
                'shipping_postal_code' => $model->shipping_postal_code,
                'shipping_region' => $model->shipping_region,
                'shipping_country' => $model->shipping_country,
                'billing_name' => $model->billing_name,
                'billing_address' => $model->billing_address,
                'billing_address_line2' => $model->billing_address_line2,
                'billing_city' => $model->billing_city,
                'billing_postal_code' => $model->billing_postal_code,
                'billing_region' => $model->billing_region,
                'billing_country' => $model->billing_country,
                'use_shipping_address_for_billing' => $model->use_shipping_address_for_billing,
            ])
            ->merge(
                collect($this->getCustomColumns())
                    ->mapWithKeys(function ($columnName) use ($model) {
                        return [$columnName => $model->{$columnName}];
                    })
                    ->toArray()
            )
            ->merge([
                'status_log' => $model->statusLog()->get()->map(fn ($statusLog) => [
                    'status' => $statusLog->status,
                    'timestamp' => $statusLog->timestamp->timestamp,
                    'data' => $statusLog->data ?? [],
                ]),
            ]);

        $order->resource = $model;
    }

    public function delete($order): void
    {
        $order->resource()->delete();
    }

    /**
     * Returns an array of custom columns the developer
     * has added to the 'orders' table.
     */
    protected function getCustomColumns(): array
    {
        return collect(Schema::getColumns((new $this->model)->getTable()))
            ->reject(fn (array $column) => in_array($column['name'], $this->knownColumns))
            ->map(fn (array $column) => $column['name'])
            ->all();
    }

    /**
     * Returns the next order number, based on the highest order number.
     */
    protected function generateOrderNumber(): int
    {
        $model = (new $this->model);

        $lastOrderNumber = $model->query()
            ->orderBy('order_number', 'DESC')
            ->value('order_number');

        if (! $lastOrderNumber) {
            return config('simple-commerce.minimum_order_number', 1000);
        }

        return $lastOrderNumber + 1;
    }

    public static function bindings(): array
    {
        return [];
    }
}
