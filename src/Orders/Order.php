<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator as CalculatorContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as Contract;
use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid as OrderPaidEvent;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSaved;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use Illuminate\Support\Facades\URL;
use Statamic\Http\Resources\API\EntryResource;

class Order implements Contract
{
    use HasData, LineItems;

    public $id;
    public $data;

    public $resource;
    protected $withoutRecalculating = false;

    public function __construct()
    {
        $this->data = collect([
            'items'          => [],
            'is_paid'        => false,
            'grand_total'    => 0,
            'items_total'    => 0,
            'tax_total'      => 0,
            'shipping_total' => 0,
            'coupon_total'   => 0,
            'published'      => false,
        ]);
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function resource($resource = null)
    {
        return $this
            ->fluentlyGetOrSet('resource')
            ->args(func_get_args());
    }

    public function billingAddress()
    {
        if ($this->has('use_shipping_address_for_billing')) {
            return $this->shippingAddress();
        }

        if (! $this->has('billing_address') && ! $this->has('billing_address_line1')) {
            return null;
        }

        return Address::from('billing', $this);
    }

    public function shippingAddress()
    {
        if (! $this->has('shipping_address') && ! $this->has('shipping_address_line1')) {
            return null;
        }

        return Address::from('shipping', $this);
    }

    public function customer($customer = null)
    {
        if ($customer !== null) {
            $this->set('customer', $customer);
            $this->save();

            return $this;
        }

        if (! $this->has('customer') || $this->get('customer') === null) {
            return null;
        }

        return Customer::find($this->get('customer'));
    }

    public function coupon($coupon = null)
    {
        if ($coupon !== null) {
            $this->set('coupon', $coupon);
            $this->save();

            return $this;
        }

        if (! $this->has('coupon') || $this->get('coupon') === null) {
            return null;
        }

        return Coupon::find($this->get('coupon'));
    }

    public function gateway()
    {
        if (is_string($this->get('gateway'))) {
            return collect(SimpleCommerce::gateways())->firstWhere('class', $this->get('gateway'));
        }

        if (is_array($this->get('gateway'))) {
            return collect(SimpleCommerce::gateways())->firstWhere('class', $this->get('gateway')['use']);
        }

        return null;
    }

    // TODO: refactor
    public function redeemCoupon(string $code): bool
    {
        $coupon = Coupon::findByCode($code);

        if ($coupon->isValid($this)) {
            $this->set('coupon', $coupon->id());
            $this->save();

            event(new CouponRedeemed($coupon));

            return true;
        }

        return false;
    }

    public function markAsPaid(): self
    {
        $this->merge([
            'is_paid'   => true,
            'paid_date' => now()->format('Y-m-d H:i'),
            'published' => true,
        ]);

        $this->save();

        event(new OrderPaidEvent($this));

        return $this;
    }

    public function receiptUrl(): string
    {
        return URL::temporarySignedRoute('statamic.simple-commerce.receipt.show', now()->addHour(), [
            'orderId' => $this->id,
        ]);
    }

    public function recalculate(): self
    {
        $calculate = resolve(CalculatorContract::class)->calculate($this);

        $this->merge($calculate);

        $this->save();

        return $this;
    }

    public function rules(): array
    {
        return []; // TODO

        // return $this->blueprint()->fields()->validator()->rules();
    }

    public function collection(): string
    {
        return SimpleCommerce::orderDriver()['collection'];
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
        if (! $this->has('items')) {
            $this->set('items', []);
            $this->save();
        }
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
        return ['data' => []]; // TODO

        return new EntryResource($this->resource());
    }

    public function toAugmentedArray(): array
    {
        $blueprintFields = $this->resource()->blueprint()->fields()->items()->reject(function ($field) {
            return $field['handle'] === 'value';
        })->pluck('handle')->toArray();

        $augmentedData = $this->resource()->toAugmentedArray($blueprintFields);

        return array_merge(
            $this->toArray(),
            $augmentedData,
        );
    }
}
