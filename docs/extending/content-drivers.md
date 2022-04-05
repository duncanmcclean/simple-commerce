---
title: Content Drivers
---

Simple Commerce has a concept of 'content drivers'. You can use content drivers to switch out how/where 'things' are stored.

Essentially, a content driver is a class which implements a certain interface. The class has all of the methods needed to allow for Simple Commerce to do its job.

You can configure content drivers for the following:

- Products
- Orders
- Coupons
- Customers

## Configuration

```php
'content' => [
    'orders' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Orders\Order::class,
        'collection' => 'orders',
    ],

    'products' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Products\Product::class,
        'collection' => 'products',
    ],

    'coupons' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Coupons\Coupon::class,
        'collection' => 'coupons',
    ],

    'customers' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\Customer::class,
        'collection' => 'customers',
    ],
],
```

As you can see, you can assign a driver class to each of the available driver types. You may also provide any additional configuration values, like `collection`, which can be used by the chosen driver.

### Low-level rundown

For those interested: here's a low-level rundown of how content drivers work:

Whenever a Simple Commerce facade is used, it looks for a binding in the service container attached to the relevant interface/contract (`DoubleThreeDigital\SimpleCommerce\Contracts\Product`).

When Simple Commerce is booted, it will automatically register a binding between the Simple Commerce interface and the chosen content driver.

## Example

Here's a bare bones example to get you started:

```php
<?php

namespace App\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as Contract;
use DoubleThreeDigital\SimpleCommerce\Data\HasData;
use Illuminate\Support\Collection;

class OrderRepository implements Contract
{
    use HasData;

    public $id;
    public $site;
    public $title;
    public $slug;
    public $data;
    public $published;

    /** @var \Illuminate\Database\Eloquent\Model $model */
    protected $model;

    public function all()
    {
        //
    }

    public function query()
    {
        //
    }

    public function find($id): self
    {
        //

        return $this;
    }

    public function create(array $data = [], string $site = ''): self
    {
        //

        return $this;
    }

    public function save(): self
    {
        //

        return $this;
    }

    public function delete()
    {
        //
    }

    public function toResource()
    {
        //
    }

    public function id()
    {
        return $this->id;
    }

    public function title($title = '')
    {
        //
    }

    public function slug($slug = '')
    {
        //
    }

    public function site($site = null)
    {
        //
    }

    public function fresh(): self
    {
        //

        return $this;
    }

    public function set(string $key, $value)
    {
        //

        return $this;
    }

    public function toArray(): array
    {
        return $this->model->toArray();
    }

    public function billingAddress()
    {
        //
    }

    public function shippingAddress()
    {
        //
    }

    public function customer($customer = '')
    {
        //
    }

    public function coupon($coupon = '')
    {
        //
    }

    public function redeemCoupon(string $code): bool
    {
        //

        return true;
    }

    public function markAsCompleted(): self
    {
        //

        return $this;
    }

    public function calculateTotals(): self
    {
        //

        return $this;
    }

    public function lineItems(): Collection
    {
        return collect();
    }

    public function lineItem($lineItemId): array
    {
        //

        return [];
    }

    public function addLineItem($lineItemData): array
    {
        //

        return [];
    }

    public function updateLineItem($lineItemId, array $lineItemData): array
    {
        //

        return [];
    }

    public function removeLineItem($lineItemId): Collection
    {
        //

        return collect();
    }

    public function clearLineItems(): Collection
    {
        //

        return collect();
    }

    public static function bindings(): array
    {
        return [];
    }
}
```
