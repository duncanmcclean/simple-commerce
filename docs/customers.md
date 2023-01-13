---
title: Customers
---

## Tags

There's a `{{ sc:customer }}` tag which lets you loop through customers & loop through a specific customers' orders. You may [review it's documentation seperatly](https://simple-commerce.duncanmcclean.com/tags/customer).

## Drivers

Simple Commerce supports three different customer drivers:

-   **Entries:** Customers will be stored as normal collection entries (Default)
-   **Users:** Customers will be stored as users - recommended if you want your customers to be able to login
-   **Database:** Used in combination with [Database Orders](/database-orders)

### Entries Driver

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

### Users Driver

Storing your customers as users can often be handy if you're building some sort of membership site or if you want a way for your customers to log in, to view order history etc.

:::tip Hot Tip
You'll need to enable [Statamic Pro](https://statamic.com/pricing) if you want to store your customers as users.
:::

To enable, change `\DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository::class` to `\DoubleThreeDigital\SimpleCommerce\Customers\UserCustomerRepository::class`.

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
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository::class, // [tl! --]
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\UserCustomerRepository::class, // [tl! ++]
    ],
],
```

You'll then want to change the fieldtype of the 'Customer' field on the Orders blueprint to a Users field. You will need to delete & create a new field to do this. You may need to also change any references to old customer entries in your Order entries to point to the correct user IDs.

### Database Driver

There's separate documentation on [Database Orders (& Customers)](/database-orders).
