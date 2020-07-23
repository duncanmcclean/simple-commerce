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
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\URL;

class CartRepository implements ContractsCartRepository
{
    public string $id = '';
    public string $title = '';
    public string $slug = '';
    public array $data = [];

    public function make(): self
    {
        $this->id = (string) Stache::generateId();
        $this->title = '#'.SimpleCommerce::freshOrderNumber();
        $this->slug = $this->id;
        $this->data = [
            'title' => $this->title,
            'items' => [],
            'is_paid' => false,
            'grand_total' => 0,
            'items_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'coupon_total' => 0,
        ];

        return $this;
    }

    public function find(string $id): self
    {
        $this->id = $id;

        $entry = $this->entry();

        $this->title = $entry->title;
        $this->slug = $entry->slug();
        $this->data = $entry->data()->toArray();

        return $this;
    }

    public function data(array $data = [])
    {
        if ($data === []) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function save(): self
    {
        $entry = Entry::make()
            ->collection(config('simple-commerce.collections.orders'))
            ->locale(Site::current()->handle())
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

        return $this;
    }

    public function entry(): EntriesEntry
    {
        $entry = Entry::find($this->id);

        if (! $entry) {
            throw new CartNotFound(__('simple-commerce::cart.cart_not_found', ['id' => $this->id]));
        }

        return $entry;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'is_paid' => isset($this->data['is_paid']) ? $this->data['is_paid'] : false,
            'paid_date' => isset($this->data['paid_date']) ? $this->data['paid_date'] : null,
            'gateway' => isset($this->data['gateway']) ? $this->data['gateway'] : null,
            'gateway_data' => isset($this->data['gateway_data']) ? $this->data['gateway_data'] : [],
            'customer' => isset($this->data['customer']) ? $this->data['customer'] : null,
            'items' => isset($this->data['items']) ? $this->data['items'] : [],
        ];
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
            'is_paid' => true,
            'paid_date' => now(),
        ]);

        $this->entry()->published(true)->save();

        event(new CustomerAddedToCart($this->entry()));

        if ($customer = Customer::find($this->data['customer'])) {
            Mail::to($customer->data['email'])
                ->send(new OrderConfirmation($this->id));
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
        $data = [
            'grand_total'       => 0000,
            'items_total'       => 0000,
            'shipping_total'    => 0000,
            'tax_total'         => 0000,
            'coupon_total'      => 0000,
        ];

        $data['items'] = collect($this->data['items'])
            ->map(function ($item) use (&$data) {
                $product = Entry::find($item['product']);

                $siteTax = collect(Config::get('simple-commerce.sites'))
                    ->get(Site::current()->handle())['tax'];

                $itemTotal = ($product->data()->get('price') * $item['quantity']);

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

        if (isset($this->data['shipping_name']) && $this->data['shipping_name'] !== null) {
            // TODO: let the user pick which method is used?
            $method = collect(Config::get('simple-commerce.sites'))
                ->get(Site::current()->handle())['shipping']['methods'][0];

            if ($method) {
                $method = new $method();
                $data['shipping_total'] = $method->calculateCost($this->entry());
            }
        }

        $data['grand_total'] = ($data['items_total'] + $data['shipping_total'] + $data['tax_total']);

        if (isset($this->data['coupon']) && $this->data['coupon'] !== null) {
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
