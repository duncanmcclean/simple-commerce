<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository as ContractsCartRepository;
use DoubleThreeDigital\SimpleCommerce\Data\Address;
use DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository;
use DoubleThreeDigital\SimpleCommerce\Events\CartCompleted;
use DoubleThreeDigital\SimpleCommerce\Events\CartSaved;
use DoubleThreeDigital\SimpleCommerce\Events\CartUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CartNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use DoubleThreeDigital\SimpleCommerce\Mail\OrderConfirmation;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class CartRepository implements ContractsCartRepository
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

        return $this;
    }

    public function save(): self
    {
        Entry::make()
            ->collection(config('simple-commerce.collections.orders'))
            ->published(false)
            ->slug($this->id)
            ->id($this->id)
            ->data($this->data)
            ->save();

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

        event(new CartUpdated($this->entry()));

        if (isset($data['customer'])) {
            event(new CustomerAddedToCart($this->entry()));
        }

        return $this;
    }

    public function entry(): EntriesEntry
    {
        $entry = Entry::find($this->id);

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
            isset($this->data['billing_zip_code']) ? $this->data['billing_zip_code'] : null,
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
            isset($this->data['shipping_note']) ? $this->data['shipping_note'] : null,
        );
    }

    public function coupon(): CouponRepository
    {
        return Coupon::find($this->data['coupon']);
    }

    public function redeemCoupon(string $code): bool
    {
        $coupon = Coupon::findByCode($code);

        if ($coupon->isValid($this->entry())) {
            $this
                ->update([
                    'coupon' => $coupon->id,
                ])
                ->calculateTotals();

            event(new CouponRedeemed($coupon->entry()));

            return true;
        }

        return false;
    }

    public function markAsCompleted(): self
    {
        $this->update([
            'is_paid'   => true,
            'paid_date' => now()->toDateTimeString(),
            'order_status' => 'completed',
        ]);

        $this->entry()->published(true)->save();

        event(new CartCompleted($this->entry()));

        if (Config::get('simple-commerce.notifications.cart_confirmation')) {
            if (isset($this->data['customer'])) {
                try {
                    $customer = Customer::find($this->data['customer']);

                    Mail::to($customer->data['email'])
                        ->send(new OrderConfirmation($this->id));
                } catch (\Exception $e) {
                    // Do nthing
                }
            }
        }

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
        if (isset($this->data['is_paid']) && $this->data['is_paid'] === true) {
            return $this;
        }

        $data = [
            'grand_total'       => 0000,
            'items_total'       => 0000,
            'shipping_total'    => 0000,
            'tax_total'         => 0000,
            'coupon_total'      => 0000,
        ];

        $data['items'] = collect($this->data['items'])
            ->map(function ($item) use (&$data) {
                $product = Product::find($item['product']);

                $siteTax = collect(Config::get('simple-commerce.sites'))
                    ->get(Site::current()->handle())['tax'];

                $itemTotal = ($product->data['price'] * $item['quantity']);

                if ($siteTax['included_in_prices']) {
                    $itemTax = str_replace(
                        '.',
                        '',
                        round(
                            ((float) substr_replace($itemTotal, '.', -2, 0) / 100) * $siteTax['rate'],
                            2
                        )
                    );

                    $itemTotal -= $itemTax;
                    $data['tax_total'] += $itemTax;
                } else {
                    $data['tax_total'] += (int) str_replace(
                        '.',
                        '',
                        round(
                            ((float) substr_replace($itemTotal, '.', -2, 0) / 100) * $siteTax['rate'],
                            2
                        )
                    );
                }

                $data['items_total'] += $itemTotal;

                return array_merge($item, [
                    'total' => $itemTotal,
                ]);
            })
            ->toArray();

        if (isset($this->data['shipping_method'])) {
            $data['shipping_total'] = Shipping::use($this->data['shipping_method'])->calculateCost($this->entry());
        }

        $data['grand_total'] = ($data['items_total'] + $data['shipping_total'] + $data['tax_total']);

        if (isset($this->data['coupon']) && $this->data['coupon'] !== null) {
            $coupon = Coupon::find($this->data['coupon']);

            if ($coupon->data['type'] === 'percentage') {
                $data['coupon_total'] = ($coupon->data['value'] * $data['grand_total']) / 100;
            }

            if ($coupon->data['type'] === 'fixed') {
                $data['coupon_total'] = ($data['grand_total'] - str_replace('.', '', $coupon->data['value']));
            }

            $data['grand_total'] = str_replace('.', '', (string) ($data['grand_total'] - $data['coupon_total']));
        }

        $this
            ->update($data)
            ->find($this->id);

        return $this;
    }
}
