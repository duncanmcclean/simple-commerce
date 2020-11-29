<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\OrderRepository as Contract;
use DoubleThreeDigital\SimpleCommerce\Data\Address;
use DoubleThreeDigital\SimpleCommerce\Events\CartSaved;
use DoubleThreeDigital\SimpleCommerce\Events\CartUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CartNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class OrderRepository implements Contract
{
    use DataRepository;

    public function make(): self
    {
        $this->id = (string) Stache::generateId();
        $this->title = '#'.SimpleCommerce::freshOrderNumber();
        $this->slug = $this->id;
        $this->data = [
            'title'          => $this->title,
            'items'          => [],
            'is_paid'        => false,
            'grand_total'    => 0,
            'items_total'    => 0,
            'tax_total'      => 0,
            'shipping_total' => 0,
            'coupon_total'   => 0,
            'order_status'   => 'cart',
        ];
        $this->site = Site::current();

        return $this;
    }

    public function save(): self
    {
        Entry::make()
            ->collection(config('simple-commerce.collections.orders'))
            ->locale($this->site)
            ->published(false)
            ->slug($this->id)
            ->id($this->id)
            ->data($this->data)
            ->save();

        // TODO: also create an OrderSaved event and deprecate the cart one
        event(new CartSaved($this->entry()));

        return $this;
    }

    public function update(array $data, bool $mergeData = true): self
    {
        if ($mergeData) {
            $data = array_merge($this->data, $data);
        }

        $this->entry()
            ->data($data)
            ->save();

        $this->find($this->id);

        // TODO: also create an OrderSaved event and deprecate the cart one
        event(new CartUpdated($this->entry()));

        if (isset($data['customer'])) {
            event(new CustomerAddedToCart($this->entry()));
        }

        return $this;
    }

    public function entry(): EntriesEntry
    {
        $entry = Entry::find($this->id);

        // TODO: throw a specific exception for orders
        if (!$entry) {
            throw new CartNotFound(__('simple-commerce::cart.cart_not_found', ['id' => $this->id]));
        }

        return $entry;
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'is_paid'          => isset($this->data['is_paid']) ? $this->data['is_paid'] : false,
            'paid_date'        => isset($this->data['paid_date']) ? $this->data['paid_date'] : null,
            'gateway'          => isset($this->data['gateway']) ? $this->data['gateway'] : null,
            'gateway_data'     => isset($this->data['gateway_data']) ? $this->data['gateway_data'] : [],
            'customer'         => isset($this->data['customer']) ? $this->data['customer'] : null,
            'items'            => isset($this->data['items']) ? $this->data['items'] : [],
            'totals' => [
                'grand_total' => isset($this->data['grand_total']) ? $this->data['grand_total'] : 0,
                'items_total' => isset($this->data['items_total']) ? $this->data['items_total'] : 0,
                'shipping_total' => isset($this->data['shipping_total']) ? $this->data['shipping_total'] : 0,
                'tax_total' => isset($this->data['tax_total']) ? $this->data['tax_total'] : 0,
                'coupon_total' => isset($this->data['coupon_total']) ? $this->data['coupon_total'] : 0,
            ],
        ];
    }

    public function billingAddress(): ?Address
    {
        if (isset($this->data['use_shipping_address_for_billing'])) {
            return $this->shippingAddress();
        }

        if (! isset($this->data['billing_address'])) {
            return null;
        }

        return new Address(
            isset($this->data['billing_name']) ? $this->data['billing_name'] : null,
            isset($this->data['billing_address']) ? $this->data['billing_address'] : null,
            isset($this->data['billing_city']) ? $this->data['billing_city'] : null,
            isset($this->data['billing_country']) ? $this->data['billing_country'] : null,
            isset($this->data['billing_zip_code']) ? $this->data['billing_zip_code'] : '',
        );
    }

    public function shippingAddress(): ?Address
    {
        if (! isset($this->data['shipping_address'])) {
            return null;
        }

        return new Address(
            isset($this->data['shipping_name']) ? $this->data['shipping_name'] : null,
            isset($this->data['shipping_address']) ? $this->data['shipping_address'] : null,
            isset($this->data['shipping_city']) ? $this->data['shipping_city'] : null,
            isset($this->data['shipping_country']) ? $this->data['shipping_country'] : null,
            isset($this->data['shipping_zip_code']) ? $this->data['shipping_zip_code'] : null,
            isset($this->data['shipping_note']) ? $this->data['shipping_note'] : '',
        );
    }

    public function coupon(): CouponRepository
    {
        return Coupon::find($this->data['coupon']);
    }
}
