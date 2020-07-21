<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository as ContractsCartRepository;
use DoubleThreeDigital\SimpleCommerce\Events\CartSaved;
use DoubleThreeDigital\SimpleCommerce\Events\CartUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerAddedToCart;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CartNotFound;
use DoubleThreeDigital\SimpleCommerce\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Illuminate\Support\Facades\URL;

class CartRepository implements ContractsCartRepository
{
    public string $id;
    public array $items = [];

    public int $grandTotal = 0000;
    public int $itemsTotal = 0000;
    public int $taxTotal = 0000;
    public int $shippingTotal = 0000;
    public int $couponTotal = 0000;

    public function make(): self
    {
        $this->id = (string) Stache::generateId();

        return $this;
    }

    public function find(string $id): self
    {
        $entry = Entry::find($id);

        if (! $entry) {
            throw new CartNotFound(__('simple-commerce::cart.cart_not_found', ['id' => $id]));
        }

        $this->id = $entry->id();
        $this->items = $entry->data()->get('items') ?? [];
        $this->grandTotal = $entry->data()->get('grand_total') ?? 0;
        $this->itemsTotal = $entry->data()->get('items_total') ?? 0;
        $this->taxTotal = $entry->data()->get('tax_total') ?? 0;
        $this->shippingTotal = $entry->data()->get('shipping_total') ?? 0;
        $this->couponTotal = $entry->data()->get('coupon_total') ?? 0;

        return $this;
    }

    public function save(): self
    {
        $entry = Entry::find($this->id);

        if ($entry === null) {
            $entry = Entry::make()
                ->collection(config('simple-commerce.collections.orders'))
                ->blueprint('order')
                ->locale(Site::current()->handle())
                ->published(false)
                ->slug($this->id)
                ->id($this->id);
        }

        $entry
            ->data([
                'title' => 'Order #'.uniqid(),
                'items' => $this->items,
                'is_paid' => false,
            ])
            ->save();

        event(new CartSaved($this->entry()));

        return $this;
    }

    public function update(array $data, bool $mergeData = true): self
    {
        $entry = Entry::find($this->id);

        if (! $entry) {
            throw new CartNotFound(__('simple-commerce::cart.cart_not_found', ['id' => $this->id]));
        }

        if ($mergeData) {
            $data = array_merge($entry->data()->toArray(), $data);
        }

        $entry
            ->data($data)
            ->save();

        event(new CartUpdated($this->entry()));

        return $this;
    }

    public function items(array $items = []): self
    {
        if ($items === []) {
            return $this->items;
        }

        $this->items = $items;

        return $this;
    }

    public function customer(string $customer = ''): self
    {
        if ($customer === '') {
            return $this->entry()->data()->get('customer');
        }

        $this->update([
            'customer' => $customer,
        ]);

        return $this;
    }

    public function count(): int
    {
        return collect($this->items)->count();
    }

    public function entry(): EntriesEntry
    {
        $entry = Entry::find($this->id);

        if (! $entry) {
            throw new CartNotFound(__('simple-commerce::cart.cart_not_found', ['id' => $this->id]));
        }

        return $entry;
    }

    public function redeemCoupon(string $code): bool
    {
        $coupon = Coupon::find(Entry::findBySlug($code, 'coupons')->id());

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
        $this
            ->entry()
            ->published(true)
            ->data(array_merge($this->entry()->data()->toArray(), [
                'is_paid' => true,
                'paid_date' => now(),
            ]))
            ->save();

        event(new CustomerAddedToCart($this->entry()));

        if ($customer = User::find($this->entry()->data()->get('customer'))) {
            Mail::to($customer->email())->send(new OrderConfirmation($this->id));
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
        $this->find($this->id);
        $entry = $this->entry();

        $data = [
            'grand_total'       => 0000,
            'items_total'       => 0000,
            'shipping_total'    => 0000,
            'tax_total'         => 0000,
            'coupon_total'      => 0000,
        ];

        $data['items'] = collect($this->items)
            ->map(function ($item) use (&$data) {
                $product = Entry::find($item['product']);

                $siteTax = collect(Config::get('simple-commerce.sites'))
                    ->get(Site::current()->handle())['tax'];

                $itemTotal = ($product->data()->get('price') * $item['quantity']);

                // if tax inclded in prices
                    // item total = item total - tax total
                    // tax total = tax total

                // if tax not included in prices
                    // item total = item total
                    // tax total = tax total


                if ($siteTax['included_in_prices']) {
                    $itemTax = str_replace(
                        '.',
                        '',
                        round(
                            ((float) substr_replace($itemTotal, '.', -2, 0) / 100 ) * $siteTax['rate'],
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

        if ($entry->data()->get('shipping_name') != null) {
            // TODO: let the user pick which method is used?

            // $address = [
            //     'name' => $entry->data()->get('shipping_name'),
            //     'address' => $entry->data()->get('shipping_address'),
            //     'city' => $entry->data()->get('shipping_city'),
            //     'country' => $entry->data()->get('shipping_country'),
            //     'zip_code' => $entry->data()->get('shipping_zip_code')
            // ];

            $method = collect(Config::get('simple-commerce.sites'))
                ->get(Site::current()->handle())['shipping']['methods'][0];

            if ($method) {
                $method = new $method();
                $data['shipping_total'] = $method->calculateCost($entry);
            }
        }

        $data['grand_total'] = ($data['items_total'] + $data['shipping_total'] + $data['tax_total']);

        if ($entry->data()->get('coupon') != null) {
            $coupon = Coupon::find($entry->data()->get('coupon'));

            if ($coupon->data['type'] === 'percentage') {
                $data['coupon_total'] += (int) str_replace(
                    '.',
                    '',
                    round(
                    ((float) substr_replace($data['grand_total'], '.', -2, 0) / 100) *
                    $coupon->data['value'],
                    2
                )
                );
            }

            if ($coupon->data['type'] === 'fixed') {
                $data['coupon_total'] = ($data['grand_total'] - str_replace('.', '', $coupon->data['value']));
            }

            $data['grand_total'] = ($data['grand_total'] - $data['coupon_total']);
        }

        $this
            ->update($data)
            ->find($this->id);

        return $this;
    }
}
