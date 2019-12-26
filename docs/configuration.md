# Configuration

When you install Commerce into your Statamic site and when you publish vendor assets, you will get a new `config/commerce.php` file which holds the configuration values for Commerce.

Some settings can be configured on a environement by environement basis by using the `.env` file.

> Eventually, we may get round to adding a Control Panel page for updating settings but for now it's just a file.

## Company Information

Commerce needs company information so we can display it on your receipts to customers or any other documents that Commerce needs to produce. The email entered in here will also act as your store's support/contact email.

```php
<?php

return [

     /**
     * Company information
     *
     * This will be shown on any receipts sent to customers.
     */
     
    'company' => [
        'name' => '',
        'address' => '',
        'city' => '',
        'country' => '',
        'zip_code' => '',
        'email' => ''
    ],
    
    ...
];
```

## Currency

Right now Commerce only supports selling products in one currency, this should be the same currency setup in your Stripe account.

Commerce asks for a currency code and asks for the currency symbol which will be displayed on the Commerce front-end.

```php
return [
    ...
    
    /**
     * Currency
     *
     * Commerce can only sell your products in a single currency.
     * By default, the currency used is Pound Sterling. You can
     * change it to any currency code supported by Stripe.
     * See: https://stripe.com/docs/currencies
     */
     
    'currency' => [
        'code' => env('COMMERCE_CURRENCY', 'gbp'),
        'symbol' => env('COMMERCE_CURRENCY_SYMBOL', '£'),
    ],
    
    ...
];
```

## Stripe

Commerce uses [Stripe](./stripe.md) as its payment gateway. In order for you to get your money, you'll need to setup your Stripe keys, which **should** be entered in your `.env` file.

You can get your Stripe API keys from  the Developer section of their Dashboard.

> Remember to use sandbox keys and sandbox cards while in testing.

```
STRIPE_SECRET=....
STRIPE_SECRET=....
```

```php
<?php

return [
  ...
  
   /**
     * Stripe
     *
     * We need these keys so your customers can purchase
     * products and so you can receive the money.
     *
     * You can find these keys here: https://dashboard.stripe.com/apikeys
     */
     
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET')
    ],
    
    ...
];
```

## Routes

We give you the option of changing the structure of any front-end route that Commerce provides.

```php
<?php

return [
    ...

    /**
         * Routes
         *
         * Commerce provides a set of web routes to make your store
         * function. You can change these routes if you have other
         * preferences.
         */
    
        'routes' => [
    
            /**
             * Cart
             *
             * - (add) Adds an item to the customers' cart.
             * - (clear) Clears all items from the customers' cart.
             * - (delete) Removes an item from the customers' cart.
             */
    
            'cart' => [
                'add' => '/cart',
                'clear' => '/cart/clear',
                'delete' => '/cart/delete',
            ],
    
            /**
             * Checkout
             *
             * - (show) Displays the checkout view to the user
             * - (store) Processes the users' order
             */
    
            'checkout' => [
                'show' => '/checkout',
                'store' => '/checkout',
            ],
    
            /**
             * Products
             *
             * - (index) Displays all products
             * - (search) Displays a product search to the user
             * - (show) Displays a product page
             */
    
            'products' => [
                'index' => '/products',
                'search' => '/products/search',
                'show' => '/products/{product}',
            ],
    
            'thanks' => '/thanks', // Page user is redirected to once order has been processed.
        ],

    ...
];
```

For example, if you wanted to change the checkout URL from being `/checkout` (the default) to `/pay-here`, you'd just change the value of `checkout.store`.

## Storage

Sometimes you might need more than just files, you might need a database. Commerce has been built in such a way where it's easy to swap out our filesystem for your own Eloquent driver.

What's even better is: you can swap out storage of products to a database while keeping your customers in flat files.

```php
<?php

return [
    ...

    /**
         * Storage
         *
         * By default, Commerce stores your files in yaml files but if you
         * want to use a database, swap the repo out for an Eloquent one.
         */
    
        'storage' => [
    
            'coupons' => [
                'repository' => FileCouponRepository::class,
                'files' => base_path().'/content/commerce/coupons',
            ],
    
            'customers' => [
                'repository' => FileCustomerRepository::class,
                'files' => base_path().'/content/commerce/customers',
            ],
    
            'order' => [
                'repository' => FileOrderRepository::class,
                'files' => base_path().'/content/commerce/orders',
            ],
    
            'products' => [
                'repository' => FileProductRepository::class,
                'files' => base_path().'/content/commerce/products',
            ],
    
        ],
];
```

> If you don't use the file repository, there's no need for the `files` key.
