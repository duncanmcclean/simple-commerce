<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as Contract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon as CouponFacade;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;

class Coupon implements Contract
{
    use HasData;

    public $id;
    public $code;
    public $data;

    protected $entry;

    public function __construct()
    {
        $this->data = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function code($id = null)
    {
        return $this
            ->fluentlyGetOrSet('code')
            ->args(func_get_args());
    }

    public function entry($entry = null)
    {
        return $this
            ->fluentlyGetOrSet('entry')
            ->args(func_get_args());
    }

    public function isValid(Order $order): bool
    {
        $order = OrderFacade::find($order->id());

        if ($this->has('minimum_cart_value') && $order->has('items_total')) {
            if ($order->get('items_total') < $this->get('minimum_cart_value')) {
                return false;
            }
        }

        if ($this->has('redeemed') && $this->has('maximum_uses') && $this->get('maximum_uses') !== null) {
            if ($this->get('redeemed') >= $this->get('maximum_uses')) {
                return false;
            }
        }

        if ($this->isProductSpecific()) {
            $couponProductsInOrder = $order->lineItems()->filter(function ($lineItem) {
                return in_array($lineItem['product'], $this->get('products'));
            });

            if ($couponProductsInOrder->count() === 0) {
                return false;
            }
        }

        if ($this->isCustomerSpecific()) {
            $isCustomerAllowed = collect($this->get('customers'))->contains($order->get('customer'));

            if (! $isCustomerAllowed) {
                return false;
            }
        }

        return true;
    }

    public function redeem(): self
    {
        $redeemed = $this->has('redeemed') ? $this->get('redeemed') : 0;

        $this->set('redeemed', $redeemed + 1);

        return $this;
    }

    protected function isProductSpecific()
    {
        return $this->has('products')
            && collect($this->get('products'))->count() >= 1;
    }

    protected function isCustomerSpecific()
    {
        return $this->has('customers')
            && collect($this->get('customers'))->count() >= 1;
    }

    public function beforeSaved()
    {
        return null;
    }

    public function afterSaved()
    {
        return null;
    }

    public function save(): self
    {
        if (method_exists($this, 'beforeSaved')) {
            $this->beforeSaved();
        }

        CouponFacade::save($this);

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete(): void
    {
        CouponFacade::delete($this);
    }

    public function fresh()
    {
        return CouponFacade::find($this->id());
    }
}
