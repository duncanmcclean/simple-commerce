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

Commerce uses Stripe as its payment gateway. In order for you to get your money, you'll need to setup your Stripe keys, which **should** be entered in your `.env` file.

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
];
```
