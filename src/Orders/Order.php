<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator as CalculatorContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as Contract;
use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid as OrderPaidEvent;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSaved;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\IsEntry;
use Illuminate\Support\Facades\URL;
use Statamic\Facades\Stache;

class Order implements Contract
{
    use IsEntry;
    use HasData;
    use LineItems;

    public $id;
    public $site;
    public $title;
    public $slug;
    public $data;
    public $published;

    protected $entry;
    protected $collection;

    public function __construct()
    {
        $this->id = Stache::generateId();
        $this->slug = $this->id;

        $this->data = [
            'title'          => '#'.SimpleCommerce::freshOrderNumber(),
            'items'          => [],
            'is_paid'        => false,
            'grand_total'    => 0,
            'items_total'    => 0,
            'tax_total'      => 0,
            'shipping_total' => 0,
            'coupon_total'   => 0,
            'order_status'   => 'cart',
        ];
    }

    public function billingAddress()
    {
        if ($this->has('use_shipping_address_for_billing')) {
            return $this->shippingAddress();
        }

        if (!$this->has('billing_address')) {
            return null;
        }

        return new Address(
            $this->has('billing_name') ? $this->get('billing_name') : null,
            $this->has('billing_adddress') ? $this->get('billing_address') : null,
            $this->has('billing_city') ? $this->get('billing_city') : null,
            $this->has('billing_country') ? $this->get('billing_country') : null,
            $this->has('billing_zip_code') ? $this->get('billing_zip_code') : null,
        );
    }

    public function shippingAddress()
    {
        if (!$this->has('shipping_address')) {
            return null;
        }

        return new Address(
            $this->has('shipping_name') ? $this->get('shipping_name') : null,
            $this->has('shipping_address') ? $this->get('shipping_address') : null,
            $this->has('shipping_city') ? $this->get('shipping_city') : null,
            $this->has('shipping_country') ? $this->get('shipping_country') : null,
            $this->has('shipping_zip_code') ? $this->get('shipping_zip_code') : null,
            $this->has('shipping_note') ? $this->get('shipping_note') : null,
        );
    }

    public function customer($customer = null)
    {
        if ($customer !== null) {
            $this->set('customer', $customer);

            return $this;
        }

        if (! $this->has('customer')) {
            return null;
        }

        return Customer::find($this->get('customer'));
    }

    public function coupon($coupon = null)
    {
        if ($coupon !== null) {
            $this->set('coupon', $coupon);

            return $this;
        }

        return Coupon::find($this->get('coupon'));
    }

    public function gateway()
    {
        return $this->has('gateway')
            ? collect(SimpleCommerce::gateways())->firstWhere('class', $this->get('gateway'))
            : null;
    }

    // TODO: refactor
    public function redeemCoupon(string $code): bool
    {
        $coupon = Coupon::findByCode($code);

        if ($coupon->isValid($this)) {
            $this->set('coupon', $coupon->id());
            event(new CouponRedeemed($coupon->entry()));

            return true;
        }

        return false;
    }

    // TODO: refactor & rename to 'markPaid'
    public function markAsPaid(): self
    {
        $this->published = true;

        $this->data([
            'is_paid'      => true,
            'paid_date'    => now()->toDateTimeString(),
            'order_status' => 'completed',
        ]);

        $this->save();

        event(new OrderPaidEvent($this));

        return $this;
    }

    // TODO: rename method
    public function buildReceipt(): string
    {
        return URL::temporarySignedRoute('statamic.simple-commerce.receipt.show', now()->addHour(), [
            'orderId' => $this->id,
        ]);
    }

    // TODO: rename method
    public function calculateTotals(): self
    {
        $calculate = resolve(CalculatorContract::class)->calculate($this);

        $this->data($calculate);

        $this->save();

        return $this;
    }

    public function beforeSaved()
    {
        if (!$this->has('items')) {
            $this->data['items'] = [];
        }
    }

    public function afterSaved()
    {
        event(new OrderSaved($this));

        // TODO: remove this event
        event(new CustomerAddedToCart($this->entry));
    }

    public function collection(): string
    {
        return config('simple-commerce.collections.orders');
    }

    public static function bindings(): array
    {
        return [];
    }
}
