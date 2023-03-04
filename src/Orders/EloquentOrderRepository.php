<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use Doctrine\DBAL\Schema\Column;
use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as CouponContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as CustomerContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\OrderRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\OrderNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
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
        return (new $this->model)->all();
    }

    public function find($id): ?Order
    {
        $model = (new $this->model)->find($id);

        if (! $model) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        return app(Order::class)
            ->resource($model)
            ->id($model->id)
            ->orderNumber($model->id)
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
            ->gateway($model->gateway)
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
            );
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
        $model->gateway = $order->gateway();

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

        $order->id = $model->id;
        $order->orderNumber = $model->id;
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
            );

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
        $tableColumns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableColumns((new $this->model)->getTable());

        return collect($tableColumns)
            ->reject(function (Column $column) {
                return in_array($column->getName(), $this->knownColumns);
            })
            ->map->getName()
            ->toArray();
    }

    public static function bindings(): array
    {
        return [];
    }
}
