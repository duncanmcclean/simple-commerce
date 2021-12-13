<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator as CalculatorContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as Contract;
use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
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
        ];
    }

    public function billingAddress()
    {
        if ($this->has('use_shipping_address_for_billing')) {
            return $this->shippingAddress();
        }

        if (! $this->has('billing_address') && ! $this->has('billing_address_line1')) {
            return null;
        }

        return new Address(
            $this->get('billing_name'),
            $this->get('billing_address') ?? $this->get('billing_address_line1'),
            $this->get('billing_address_line2'),
            $this->get('billing_city'),
            $this->get('billing_country'),
            $this->get('billing_zip_code') ?? $this->get('billing_postal_code'),
            $this->get('billing_region')
        );
    }

    public function shippingAddress()
    {
        if (! $this->has('shipping_address') && ! $this->has('shipping_address_line1')) {
            return null;
        }

        return new Address(
            $this->get('shipping_name'),
            $this->get('shipping_address') ?? $this->get('shipping_address_line1'),
            $this->get('shipping_address_line2'),
            $this->get('shipping_city'),
            $this->get('shipping_country'),
            $this->get('shipping_zip_code') ?? $this->get('shipping_postal_code'),
            $this->get('shipping_region')
        );
    }

    public function customer($customer = null)
    {
        if ($customer !== null) {
            $this->set('customer', $customer);

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

            return $this;
        }

        if (! $this->has('coupon') || $this->get('coupon') === null) {
            return null;
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
            event(new CouponRedeemed($coupon));

            return true;
        }

        return false;
    }

    public function markAsPaid(): self
    {
        $this->published = true;

        $this->data([
            'is_paid'   => true,
            'paid_date' => now()->format('Y-m-d H:i'),
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

        $this->data($calculate);

        $this->save();

        return $this;
    }

    public function rules(): array
    {
        return $this->blueprint()->fields()->validator()->rules();
    }

    public function beforeSaved()
    {
        if (! $this->has('items')) {
            $this->data['items'] = [];
        }
    }

    public function afterSaved()
    {
        event(new OrderSaved($this));
    }

    public function collection(): string
    {
        return SimpleCommerce::orderDriver()['collection'];
    }

    public static function bindings(): array
    {
        return [];
    }
}
