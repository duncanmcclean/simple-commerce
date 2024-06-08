<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Contracts\Coupon as CouponContract;
use DuncanMcClean\SimpleCommerce\Contracts\Customer as CustomerContract;
use DuncanMcClean\SimpleCommerce\Contracts\Order as Contract;
use DuncanMcClean\SimpleCommerce\Data\HasData;
use DuncanMcClean\SimpleCommerce\Events\CouponRedeemed;
use DuncanMcClean\SimpleCommerce\Events\OrderSaved;
use DuncanMcClean\SimpleCommerce\Events\OrderStatusUpdated;
use DuncanMcClean\SimpleCommerce\Events\PaymentStatusUpdated;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order as OrderFacade;
use DuncanMcClean\SimpleCommerce\Http\Resources\BaseResource;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\Calculator;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Statamic\Facades\Site as FacadesSite;
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

    public function itemsTotalWithTax(): int
    {
        return $this->lineItems()->sum(function (LineItem $lineItem) {
            return $lineItem->totalIncludingTax();
        });
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

    public function shippingTotalWithTax(): int
    {
        $shippingTotal = $this->shippingTotal();
        $shippingTax = $this->get('shipping_tax');

        return $shippingTotal + $shippingTax['amount'];
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
                    return $value;
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
                    return $value;
                }

                return Coupon::find($value);
            })
            ->args(func_get_args());
    }

    public function gatewayData(?string $gateway = null, ?array $data = null, ?array $refund = null): ?GatewayData
    {
        if ($gateway) {
            $this->gateway = array_merge($this->gateway ?? [], ['use' => $gateway]);
        }

        if ($data) {
            $this->gateway = array_merge($this->gateway ?? [], ['data' => $data]);
        }

        if ($refund) {
            $this->gateway = array_merge($this->gateway ?? [], ['refund' => $refund]);
        }

        if (! $this->gateway) {
            return null;
        }

        return new GatewayData($this->gateway);
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

        // We don't really know what site this order belongs to. For now, we'll just return the default site.
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            return FacadesSite::current();
        }

        return null;
    }

    public function billingAddress(): ?Address
    {
        if ($this->get('use_shipping_address_for_billing', false)) {
            return $this->shippingAddress();
        }

        if (
            ! $this->has('billing_address')
            && ! $this->has('billing_address_line1')
            && ! $this->has('billing_address_line2')
            && ! $this->has('billing_city')
        ) {
            return null;
        }

        return Address::from('billing', $this);
    }

    public function shippingAddress(): ?Address
    {
        if (
            ! $this->has('shipping_address')
            && ! $this->has('shipping_address_line1')
            && ! $this->has('shipping_address_line2')
            && ! $this->has('shipping_city')
        ) {
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

            event(new CouponRedeemed($coupon, $this));

            return true;
        }

        return false;
    }

    public function updateOrderStatus(OrderStatus $orderStatus, array $data = []): self
    {
        $this
            ->status($orderStatus)
            ->appendToStatusLog($orderStatus, $data)
            ->save();

        event(new OrderStatusUpdated($this, $orderStatus));

        return $this;
    }

    public function updatePaymentStatus(PaymentStatus $paymentStatus, array $data = []): self
    {
        $this
            ->paymentStatus($paymentStatus)
            ->appendToStatusLog($paymentStatus, $data)
            ->save();

        event(new PaymentStatusUpdated($this, $paymentStatus));

        return $this;
    }

    public function statusLog(): Collection|string|null
    {
        // Convert the old format to the new format. We can probably remove this in the future.
        if (! empty($this->get('status_log')) && ! is_array(Arr::first($this->get('status_log')))) {
            $this->set('status_log', collect($this->get('status_log'))->map(function ($date, $status) {
                return new StatusLogEvent(
                    status: $status,
                    timestamp: Carbon::parse($date)->timestamp
                );
            })->values()->toArray());
        }

        return collect($this->get('status_log'))->map(function (array $statusLogEvent) {
            return new StatusLogEvent(
                status: $statusLogEvent['status'],
                timestamp: $statusLogEvent['timestamp'],
                data: $statusLogEvent['data'] ?? [],
            );
        });
    }

    public function statusLogIncludes(OrderStatus|PaymentStatus $status): bool
    {
        return $this->statusLog()->contains(fn (StatusLogEvent $statusLogEvent) => $statusLogEvent->status->is($status));
    }

    public function appendToStatusLog(OrderStatus|PaymentStatus $status, array $data = []): self
    {
        $statusLog = $this->statusLog()->push(new StatusLogEvent(
            status: $status,
            timestamp: Carbon::now()->timestamp,
            data: $data,
        ));

        $this->set('status_log', $statusLog->toArray());

        return $this;
    }

    public function refund($refundData): self
    {
        $this->updatePaymentStatus(PaymentStatus::Refunded);

        $this->gatewayData(refund: $refundData);

        return $this;
    }

    public function recalculate(): self
    {
        if ($this->paymentStatus()->is(PaymentStatus::Paid)) {
            return $this;
        }

        $calculation = tap(Calculator::calculate($this))->save();

        return $calculation->fresh();
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
        return $this->resource()->toAugmentedArray($keys);
    }

    public function toAugmentedCollection($keys = null): Collection
    {
        return $this->resource()->toAugmentedCollection($keys);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
