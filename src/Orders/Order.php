<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator as CalculatorContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as CouponContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as CustomerContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as Contract;
use DoubleThreeDigital\SimpleCommerce\Data\HasData;
use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid as OrderPaidEvent;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSaved;
use DoubleThreeDigital\SimpleCommerce\Events\OrderShipped as OrderShippedEvent;
use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\PaymentStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use DoubleThreeDigital\SimpleCommerce\Http\Resources\BaseResource;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Statamic\Contracts\Entries\Entry;
use Statamic\Http\Resources\API\EntryResource;
use Statamic\Sites\Site;

class Order implements Contract
{
    use HasData, HasLineItems;

    public $id;
    public $orderNumber;
    public $status;
    public $paymentStatus;
    public $lineItems;
    public $grandTotal;
    public $itemsTotal;
    public $taxTotal;
    public $shippingTotal;
    public $couponTotal;
    public $customer;
    public $coupon;
    public $gateway;
    public $data;
    public $resource;

    protected $withoutRecalculating = false;

    public function __construct()
    {
        $this->status = OrderStatus::Cart;
        $this->paymentStatus = PaymentStatus::Unpaid;
        $this->lineItems = collect();

        $this->grandTotal = 0;
        $this->itemsTotal = 0;
        $this->taxTotal = 0;
        $this->shippingTotal = 0;
        $this->couponTotal = 0;

        $this->data = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function orderNumber($orderNumber = null)
    {
        return $this
            ->fluentlyGetOrSet('orderNumber')
            ->args(func_get_args());
    }

    public function status($status = null)
    {
        return $this
            ->fluentlyGetOrSet('status')
            ->setter(function ($value) {
                if (is_string($value)) {
                    return OrderStatus::from($value);
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function paymentStatus($paymentStatus = null)
    {
        return $this
            ->fluentlyGetOrSet('paymentStatus')
            ->setter(function ($value) {
                if (is_string($value)) {
                    return PaymentStatus::from($value);
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function grandTotal($grandTotal = null)
    {
        return $this
            ->fluentlyGetOrSet('grandTotal')
            ->args(func_get_args());
    }

    public function itemsTotal($itemsTotal = null)
    {
        return $this
            ->fluentlyGetOrSet('itemsTotal')
            ->args(func_get_args());
    }

    public function taxTotal($taxTotal = null)
    {
        return $this
            ->fluentlyGetOrSet('taxTotal')
            ->args(func_get_args());
    }

    public function shippingTotal($shippingTotal = null)
    {
        return $this
            ->fluentlyGetOrSet('shippingTotal')
            ->args(func_get_args());
    }

    public function couponTotal($couponTotal = null)
    {
        return $this
            ->fluentlyGetOrSet('couponTotal')
            ->args(func_get_args());
    }

    public function customer($customer = null)
    {
        return $this
            ->fluentlyGetOrSet('customer')
            ->setter(function ($value) {
                if (! $value) {
                    return null;
                }

                if ($value instanceof CustomerContract) {
                    return $value->id();
                }

                return Customer::find($value);
            })
            ->args(func_get_args());
    }

    public function coupon($coupon = null)
    {
        return $this
            ->fluentlyGetOrSet('coupon')
            ->setter(function ($value) {
                if (! $value) {
                    return null;
                }

                if ($value instanceof CouponContract) {
                    return $value->id();
                }

                return Coupon::find($value);
            })
            ->args(func_get_args());
    }

    public function gateway($gateway = null)
    {
        return $this
            ->fluentlyGetOrSet('gateway')
            ->args(func_get_args());
    }

    public function currentGateway(): ?array
    {
        if (is_string($this->gateway())) {
            return collect(SimpleCommerce::gateways())->firstWhere('class', $this->gateway());
        }

        if (is_array($this->gateway())) {
            return collect(SimpleCommerce::gateways())->firstWhere('class', $this->gateway()['use']);
        }

        return null;
    }

    public function resource($resource = null)
    {
        return $this
            ->fluentlyGetOrSet('resource')
            ->args(func_get_args());
    }

    public function site(): ?Site
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $this->resource()->site();
        }

        return null;
    }

    public function billingAddress(): ?Address
    {
        if ($this->get('use_shipping_address_for_billing', false)) {
            return $this->shippingAddress();
        }

        if (! $this->has('billing_address') && ! $this->has('billing_address_line1')) {
            return null;
        }

        return Address::from('billing', $this);
    }

    public function shippingAddress(): ?Address
    {
        if (! $this->has('shipping_address') && ! $this->has('shipping_address_line1')) {
            return null;
        }

        return Address::from('shipping', $this);
    }

    public function redeemCoupon(string $code): bool
    {
        $coupon = Coupon::findByCode($code);

        if ($coupon->isValid($this)) {
            $this->coupon($coupon);
            $this->save();

            event(new CouponRedeemed($coupon));

            return true;
        }

        return false;
    }

    public function updateOrderStatus(OrderStatus $orderStatus): self
    {
        $this->status($orderStatus)->save();

        event(new OrderStatusUpdated($this, $orderStatus));

        if ($orderStatus->is(OrderStatus::Shipped)) {
            $this
                ->merge([
                    'shipped_date'  => now()->format('Y-m-d H:i'),
                ])
                ->save();

            event(new OrderShippedEvent($this));
        }

        return $this;
    }

    public function updatePaymentStatus(PaymentStatus $paymentStatus): self
    {
        $this->paymentStatus($paymentStatus)->save();

        if ($paymentStatus->is(PaymentStatus::Paid)) {
            $this
                ->merge([
                    'paid_date' => now()->format('Y-m-d H:i'),
                    'published' => true,
                ])
                ->save();

            event(new OrderPaidEvent($this));
        }

        event(new PaymentStatusUpdated($this, $paymentStatus));

        return $this;
    }

    public function refund($refundData): self
    {
        $this->updatePaymentStatus(PaymentStatus::Refunded);

        if (is_string($this->gateway())) {
            $data = [
                'use' => $this->gateway(),
                'refund' => $refundData,
            ];
        } elseif (is_array($this->gateway())) {
            $data = array_merge($this->gateway(), [
                'refund' => $refundData,
            ]);
        }

        $this->gateway($data);

        return $this;
    }

    public function recalculate(): self
    {
        $calculate = resolve(CalculatorContract::class)->calculate($this);

        $this->lineItems($calculate['items']);

        $this->grandTotal($calculate['grand_total']);
        $this->itemsTotal($calculate['items_total']);
        $this->taxTotal($calculate['tax_total']);
        $this->shippingTotal($calculate['shipping_total']);
        $this->couponTotal($calculate['coupon_total']);

        $this->merge(Arr::except($calculate, 'items'));

        $this->save();

        return $this;
    }

    public function withoutRecalculating(callable $callback)
    {
        $this->withoutRecalculating = true;

        $return = $callback();

        $this->withoutRecalculating = false;

        return $return;
    }

    public function beforeSaved()
    {
        //
    }

    public function afterSaved()
    {
        event(new OrderSaved($this));
    }

    public function save(): self
    {
        if (method_exists($this, 'beforeSaved')) {
            $this->beforeSaved();
        }

        OrderFacade::save($this);

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete(): void
    {
        OrderFacade::delete($this);
    }

    public function fresh(): self
    {
        $freshOrder = OrderFacade::find($this->id());

        $this->id = $freshOrder->id;
        $this->status = $freshOrder->status;
        $this->paymentStatus = $freshOrder->paymentStatus;
        $this->lineItems = $freshOrder->lineItems;
        $this->grandTotal = $freshOrder->grandTotal;
        $this->itemsTotal = $freshOrder->itemsTotal;
        $this->taxTotal = $freshOrder->taxTotal;
        $this->shippingTotal = $freshOrder->shippingTotal;
        $this->couponTotal = $freshOrder->couponTotal;
        $this->customer = $freshOrder->customer;
        $this->coupon = $freshOrder->coupon;
        $this->gateway = $freshOrder->gateway;
        $this->data = $freshOrder->data;
        $this->resource = $freshOrder->resource;

        return $this;
    }

    public function toArray(): array
    {
        $toArray = $this->data->toArray();

        $toArray['id'] = $this->id();

        return $toArray;
    }

    public function toResource()
    {
        if (isset(SimpleCommerce::orderDriver()['collection'])) {
            return new EntryResource($this->resource());
        }

        return new BaseResource($this);
    }

    public function toAugmentedArray($keys = null): array
    {
        if ($this->resource() instanceof Entry) {
            $blueprintFields = $this->resource()->blueprint()->fields()->items()->reject(function ($field) {
                return isset($field['import']) || $field['handle'] === 'value';
            })->pluck('handle')->toArray();

            $augmentedData = $this->resource()->toAugmentedArray($blueprintFields);

            return array_merge(
                $this->toArray(),
                $augmentedData,
            );
        }

        if ($this->resource() instanceof Model) {
            $resource = \DoubleThreeDigital\Runway\Runway::findResourceByModel($this->resource());

            return $resource->augment($this->resource());
        }

        return [];
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
