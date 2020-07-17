<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository as ContractsCartRepository;
use DoubleThreeDigital\SimpleCommerce\Events\CartSaved;
use DoubleThreeDigital\SimpleCommerce\Events\CartUpdated;
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
use Stripe\Coupon;

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
        $cart = Entry::find($id);

        $this->id = $cart->id();
        $this->items = $cart->data()->get('items') ?? [];
        $this->grandTotal = $cart->data()->get('grand_total') ?? 0;
        $this->itemsTotal = $cart->data()->get('items_total') ?? 0;
        $this->taxTotal = $cart->data()->get('tax_total') ?? 0;
        $this->shippingTotal = $cart->data()->get('shipping_total') ?? 0;
        $this->couponTotal = $cart->data()->get('coupon_total') ?? 0;

        return $this;
    }

    public function save(): self
    {
        $entry = Entry::find($this->id);

        if ($entry === null) {
            $entry = Entry::make()
                ->collection('orders')
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
            throw new CartNotFound("We could not find a cart with the ID of {$this->id}.");
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

    public function count(): int
    {
        return collect($this->items)->count();
    }

    public function entry(): EntriesEntry
    {
        $entry = Entry::find($this->id);

        if (! $entry) {
            throw new CartNotFound("We could not find a cart with the ID of {$this->id}.");
        }

        return $entry;
    }

    public function attachCustomer($user): self
    {
        $this
            ->entry()
            ->set('customer', $user->id())
            ->save();

        event(new CustomerAddedToCart($this->entry()));    

        return $this;    
    }

    public function redeemCoupon(string $code): bool
    {
        $coupon = Coupon::find(Entry::findBySlug($code)->id());

        if ($coupon->isValid($this->entry())) {
            $this
                ->update([
                    'coupon' => $coupon->id,
                ])
                ->calculateTotals();

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
        Mail::to(User::find($this->entry()->data()->get('customer'))->email())->send(new OrderConfirmation($this->id));

        return $this;
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

                if (! $siteTax['included_in_prices']) {
                    $data['tax_total'] += (int) str_replace('.', '', round(
                        ((float) substr_replace($itemTotal, '.', -2, 0) / 100) * 
                        $siteTax['rate'], 2)
                    );
                }

                // TODO: coupon

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

        $data['grand_total'] = ($data['items_total'] + $data['shipping_total'] + $data['tax_total'] + $data['coupon_total']); 

        $this
            ->update($data)
            ->find($this->id);

        return $this;
    }
}