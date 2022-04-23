---
title: Content Drivers
---

Simple Commerce has a concept of 'content drivers'. They let you switch out how & where 'things' in Simple Commerce are stored.

A content driver consists of a 'repository' which implements a couple of methods to handle creating/finding/saving bits of content. They work in a very similar way to [Repositories](https://statamic.dev/extending/repositories) in Statamic itself.

The Database Orders feature is essentially two content drivers which deal with getting content from the database.

You can configure content drivers for the following 'things':

- Products
- Orders
- Coupons
- Customers

## Configuration

```php
'content' => [
    'coupons' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Coupons\EntryCouponRepository::class,
        'collection' => 'coupons',
    ],

    'customers' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository::class,
        'collection' => 'customers',
    ],

    'orders' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository::class,
        'collection' => 'orders',
    ],

    'products' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Products\EntryProductRepository::class,
        'collection' => 'products',
    ],
],
```

As you can see, each bit of 'content' has its own 'repository' attached, along with any configuration options that may be needed.

## Example

Here's a bare-bones example of a content driver repository to get you started:

```php
<?php

namespace DoubleThreeDigital\SimpleCommerce\Products;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use DoubleThreeDigital\SimpleCommerce\Contracts\ProductRepository as RepositoryContract;

class EntryProductRepository implements RepositoryContract
{
    public function all()
    {
        return [];
    }

    public function find($id): ?Product
    {
        //
    }

    public function make(): Product
    {
        return app(Product::class);
    }

    public function save(Product $product): void
    {
        //
    }

    public function delete(Product $product): void
    {
        //
    }

    public static function bindings(): array
    {
        return [];
    }
}
```

You may find it helpful to review the built-in content driver repositories while building a custom one.
