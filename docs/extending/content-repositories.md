---
title: Content Repositories
---

Content Repositories let you switch out how & where 'thing' in Simple Commerce are stored - they're pretty similar to Statamic's [Repositories](https://statamic.dev/extending/repositories) concept.

The [Database Orders](/database-orders) functionality is essentially two repositories which deal with getting/saving content from the database.

You can configure content repositories for the following 'things':

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

As you can see, each bit of 'content' has its own repository set, along with any configuration options that may be needed.

## Example

Here's a bare-bones example of a content repository to get you started:

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

You may find it helpful to review the built-in content repositories when building a custom one.
