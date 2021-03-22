<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as Contract;
use DoubleThreeDigital\SimpleCommerce\Events\CartCompleted;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class EloquentOrder extends Model implements Contract
{
    protected $fillable = [
        'title', 'slug', 'items', 'gateway_data', 'is_paid', 'customer', 'coupon', 'published',
    ];

    public function find(string $id): self
    {
        return parent::find($id);
    }

    public function create(array $data = [], string $site = ''): self
    {
        return parent::create($data);
    }

    public function toResource()
    {
        return null;
    }

    public function id()
    {
        return $this->id;
    }

    public function title(string $title = '')
    {
        if ($title !== '') {
            $this->forceFill([
                'title' => $title,
            ]);
        }

        return $this->title;
    }

    public function slug(string $slug = '')
    {
        if ($slug !== '') {
            $this->forceFill([
                'slug' => $slug,
            ]);
        }

        return $this->slug;
    }

    public function site($site = null): self
    {
        if ($site !== '') {
            $this->forceFill([
                'site' => $site,
            ]);
        }

        return $this->site;
    }

    public function data(array $data = [])
    {
        return $this->toArray();
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

    public function customer(string $customer = '')
    {
        if ($customer !== '') {
            $this->forceFill([
                'customer' => $customer,
            ]);
        }

        return Customer::find($this->customer);
    }

    public function coupon(string $coupon = '')
    {
        if ($coupon !== '') {
            $this->forceFill([
                'coupon' => $coupon,
            ]);
        }

        return Coupon::find($this->coupon);
    }

    public function redeemCoupon(string $code): bool
    {
        // TODO

        return false;
    }

    public function markAsCompleted(): self
    {
        $this->update([
            'published'    => false,
            'is_paid'      => true,
            'paid_date'    => now()->toDateTimeString(),
            'order_status' => 'completed',
        ]);

        event(new CartCompleted($this));

        // TODO: emails

        return $this;
    }

    public function buildReceipt(): string
    {
        return URL::temporarySignedRoute('statamic.simple-commerce.receipt.show', now()->addHour(), [
            'orderId' => $this->id,
        ]);
    }

    public function calculateTotals(): self
    {
        $calculate = resolve(Calculator::class)->calculate($this);

        $this->update($calculate);

        return $this;
    }

    public static function bindings(): array
    {
        return [];
    }
}
