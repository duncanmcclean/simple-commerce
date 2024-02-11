---
title: Customers
---

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
        'repository' => \DuncanMcClean\SimpleCommerce\Customers\Customer::class,
        'collection' => 'customers', // [tl! --]
        'collection' => 'members', // [tl! ++]
    ],
],
```

### Users Driver

Storing your customers as users can often be handy if you're building some sort of membership site or if you want a way for your customers to log in, view order history etc.

:::tip Hot Tip
You'll need to enable [Statamic Pro](https://statamic.com/pricing) if you want to store your customers as users.
:::

To enable, change `\DuncanMcClean\SimpleCommerce\Customers\EntryCustomerRepository::class` to `\DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository::class`.

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
        'repository' => \DuncanMcClean\SimpleCommerce\Customers\EntryCustomerRepository::class, // [tl! --]
        'repository' => \DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository::class, // [tl! ++]
    ],
],
```

You'll then want to change the fieldtype of the 'Customer' field on the Orders blueprint to a Users field. You will need to delete & create a new field to do this. You may need to also change any references to old customer entries in your Order entries to point to the correct user IDs.

### Database Driver

This is where you store your customers in the database. Simple Commerce supports this when using the [Database Orders](/database-orders) feature.

## Templating

### Get orders made by a customer

You can loop through orders made by a specific customer using the Antlers Tag. All you need to do is provide the ID of the customer.

If you're using the [#entries] driver, that'll be the entry ID of your customer or if you're storing customers as users, that might be the ID of the currently logged in user.

```antlers
{{ sc:customer:orders customer="customer-id-goes-here" }}
  {{ title }} - {{ grand_total }}
{{ /sc:customer:orders }}
```

### More information

For more information about the available Customer tags, please review the [`{{ sc:customer }}` tag documentation](/tags/customer).
