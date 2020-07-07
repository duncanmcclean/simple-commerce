<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Mail\OrderConfirmation;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\User;

class CartRepository
{
    // TODO: create the big boi interface for the repo

    public string $id;
    public array $items = [];

    public int $grandTotal = 0000;
    public int $itemsTotal = 0000;
    public int $taxTotal = 0000;
    public int $shippingTotal = 0000;
    public int $couponTotal = 0000;

    public function make()
    {
        $this->id = (string) Stache::generateId();

        return $this;
    }

    public function find(string $id)
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

    public function save()
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

        return $this;    
    }

    public function update(array $data, bool $mergeData = true)
    {
        $entry = Entry::find($this->id);  

        if (! $entry) {
            throw new Exception('Cart not found');
        }

        if ($mergeData) {
            $data = array_merge($entry->data()->toArray(), $data);
        }

        $entry
            ->data($data)
            ->save();

        return $this;    
    }

    public function items(array $items = [])
    {
        if ($items === []) {
            return $this->items;
        }

        $this->items = $items;

        return $this;
    }

    public function count()
    {
        return collect($this->items)->count();
    }

    public function entry()
    {
        return Entry::find($this->id);
    }

    public function attachCustomer($user)
    {
        $this
            ->entry()
            ->set('customer', $user->id())
            ->save();

        return $this;    
    }

    public function markAsCompleted()
    {
        $this
            ->entry()
            ->published(true)
            ->data(array_merge($this->entry()->data()->toArray(), [
                'is_paid' => true,
                'paid_date' => now(),
            ]))
            ->save();

        Mail::to(User::find($this->entry()->data()->get('customer'))->email())->send(new OrderConfirmation($this->id));

        return $this;
    }

    public function calculateTotals()
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