---
title: Customers
---

Customers are always right...

## Storage

By default, customers are stored as Statamic Entries, similar to orders, products and coupons.

They live in their own `customers` collection, which you can change if you need to in the `simple-commerce.php` config file.

```php
/*
|--------------------------------------------------------------------------
| Content Drivers
|--------------------------------------------------------------------------
|
| Normally, all of your products, orders, coupons & customers are stored as flat
| file entries. This works great for small stores where you want to keep everything
| simple. However, for more complex stores, you may want store your data somewhere else
| (like a database). Here's where you'd swap that out.
|
| https://simple-commerce.duncanmcclean.com/extending/content-drivers
|
*/

'content' => [
	// All the other bits..

    'customers' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\Customer::class,
        'collection' => 'customers', // [tl! --]
        'collection' => 'members', // [tl! ++]
    ],
],
```

Alternatively, you can store your customers as Statamic users.

This is often pretty handy if you're building some sort of membership site or if you want a way for your customers to log in, to view order history etc.

> **Hot Tip:** You'll need to enable [Statamic Pro](https://statamic.com/pricing) if you want to store your customers as users.

To enable, change `\DoubleThreeDigital\SimpleCommerce\Customers\Customer::class` to `\DoubleThreeDigital\SimpleCommerce\Customers\UserCustomer::class`

```php
/*
|--------------------------------------------------------------------------
| Content Drivers
|--------------------------------------------------------------------------
|
| Normally, all of your products, orders, coupons & customers are stored as flat
| file entries. This works great for small stores where you want to keep everything
| simple. However, for more complex stores, you may want store your data somewhere else
| (like a database). Here's where you'd swap that out.
|
| https://simple-commerce.duncanmcclean.com/extending/content-drivers
|
*/
'content' => [
	// All the other bits..

    'customers' => [
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\Customer::class, // [tl! --]
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\UserCustomer::class, // [tl! ++]
    ],
],
```

## Tags

The `{{ sc:customer }}` tag is documented seperatly, [see docs](https://simple-commerce.duncanmcclean.com/tags/customer).
