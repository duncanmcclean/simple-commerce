<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as Contract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\IsEntry;
use Statamic\Facades\Entry;

class Coupon implements Contract
{
    use IsEntry;
    use HasData;

    public $id;
    public $site;
    public $title;
    public $slug;
    public $data;
    public $published;

    protected $entry;
    protected $collection;

    public function findByCode(string $code): self
    {
        $entry = Entry::findBySlug($code, config('simple-commerce.collections.coupons'));

        if (!$entry) {
            throw new CouponNotFound(__('simple-commerce.coupons.coupon_not_found'));
        }

        return $this->find($entry->id());
    }

    public function code(): string
    {
        return $this->slug();
    }

    // TODO: refactor
    public function isValid(Order $order): bool
    {
        $order = Order::find($order->id());

        if ($this->has('minimum_cart_value') && $order->has('items_total')) {
            if ($order->get('items_total') < $this->get('minimum_cart_value')) {
                return false;
            }
        }

        if ($this->has('redeemed') && $this->has('maximum_uses')) {
            if ($this->has('redeemed') >= $this->get('maximum_uses')) {
                return false;
            }
        }

        if ($this->isProductSpecific()) {
            $couponProductsInOrder = $order->lineItems()->filter(function ($lineItem) {
                return in_array($lineItem['product'], $this->get('products'));
            });

            if ($couponProductsInOrder === 0) {
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

    public function collection(): string
    {
        return config('simple-commerce.collections.coupons');
    }

    protected function isProductSpecific()
    {
        return $this->has('products')
            && collect($this->get('products'))->count() >= 1;
    }

    public static function bindings(): array
    {
        return [];
    }
}
