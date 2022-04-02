---
title: Database Orders
---

After a couple thousand (or maybe a couple hundred) of orders, your site can start to feel slow when storing orders as entries. However, there is a solution! Using a database.

But wait.. you say. Isn't Statamic a flat-file CMS? The answer is yes but when your site starts to scale, you can run into bottlenecks. Databases have proven to be able to scale well and are easy for querying.

### When should I switch to a database?

- You have a few hundred/thousand orders and you're starting to see performance suffer
- You need to do some kind of complicated queries against your orders
- You want to keep your orders out of version control

### How it works

So, instead of your orders & customers living as entries in your `content` folder, they will live in your database. Simple Commerce uses [Eloquent](https://laravel.com/docs/master/eloquent) in order to talk with the database (Eloquent is a Laravel thing).

## Switching to a database

> Note: The below steps assume you already have a database setup & have ran the `php artisan migrate` column to create the default tables.

TODO: make this process a little more seemless - these steps are scrappy, here until I write something better 😅

1. Copy the Laravel migrations from Simple Commerce `cp vendor/doublethreedigital/simple-commerce/database/migrations/* database/migrations`
2. Run `php artisan migrate`
3. Switch the 'repositories' around:

```php
// config/simple-commerce.php

'content' => [
    'orders' => [
        'collection' => 'orders', // [tl! --]
        'repository' => \DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository::class, // [tl! --]

        'repository' => \DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository::class, // [tl! ++]
        'model' => \DoubleThreeDigital\SimpleCommerce\Orders\OrderModel::class, // [tl! ++]
    ],

    'customers' => [
        'collection' => 'customers', // [tl! --]
        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository::class, // [tl! --]

        'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\EloquentCustomerRepository::class, // [tl! ++]
        'model' => \DoubleThreeDigital\SimpleCommerce\Customers\CustomerModel::class, // [tl! ++]
    ],

    // ...
],
```

4. Copy the blueprints (TODO: need to make special blueprint stubs for the user to copy - can probably also automate this step)

5. Make changes to blueprints (change title to `order_number`, make customer a Runway belongsTo, remove slug, mark pretty much everything as read_only)

6. Install Runway:

```bash
composer require doublethreedigital/runway
php artisan vendor:publish --tag="runway-config"
```

7. Replace the `resources` section of the Runway config file with the following:

```php
'resources' => [
    \DoubleThreeDigital\SimpleCommerce\Customers\CustomerModel::class => [
        'name' => 'Customers',
        'blueprint' => 'customers',
    ],

    \DoubleThreeDigital\SimpleCommerce\Orders\OrderModel::class => [
        'name' => 'Orders',
        'blueprint' => 'order',
    ],
],
```

8. If you have existing orders & customers, you'll now want to migrate those to the database. Right now you'll need to write a custom 'migrate script' to handle this. (TODO: handle this step for the users - prompt for old collection names & move everything across)

## Control Panel interface

When you make the switch, Simple Commerce will install [Runway](https://statamic.com/runway), another addon by me (Duncan McClean). Runway is the thing which lets you manage your database records/Eloquent models in the Control Panel.

Runway has it's own documentation site - you may [read it if you please](https://runway.duncanmcclean.com/control-panel).

## Overriding

If you want to add a custom column to the Orders/Customers table, then you'll want to override both the Eloquent model & the repository.

### The Model

TODO config change, override class, add new column to fillable, make sure you've done a migration

### The 'Repository'

TODO config change, copy make/save methods and just add what you need
