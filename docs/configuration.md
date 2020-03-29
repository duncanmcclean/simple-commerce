# Configuration

Simple Commerce gives you a lot of settings, some can be updated in the Control Panel, others in the config file, located in `config/simple-commerce.php`.

## Control Panel

Order Statuses, Shipping Zones and Tax Rates are some of the things that Simple Commerce allows you to configure from the Control Panel.

You can manage them from the `Settings` navigation item under the `Simple Commerce` section.

## `config/simple-commerce.php`

When you install Simple Commerce, it publishes it's own configuration file for you. This configuration file gives you the ultimate control over how Simple Commerce works.

### Address

```php
<?php

return [

    /**
        * Business Address
        *
        * Address information for your business. By default,
        * this will be used as the location to set tax and
        * shipping prices.
    */
    
    'address' => [
        'address_1' => '',
        'address_2' => '',
        'address_3' => '',
        'city' => '',
        'country' => '',
        'state' => '',
        'zip_code' => '',
    ],

];
```

In order for Simple Commerce to calculate taxes and shipping prices properly, you'll need to enter information about where your business is located.

### Gateways

```php
<?php

return [

    /**
         * Payment Gateways
         *
         * Simple Commerce gives you the ability to
         * configure different payment gateways.
    */
    
    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
//        \DoubleThreeDigital\SimpleCommerce\Gateways\StripeGateway::class => [],
    ],

];
```

Simple Commerce comes out of the box with a number of popular [payment gateways](./gateways.md). Some of which you'll want to use for your store, some of which you may not.

To enable a gateway, just add the class of the gateway you wish to enable. In the example, the `DummyGateway` is enabled.

If you want to learn more about Payment Gateways, read [our documentation](./gateways.md) on it.

### Currency

```php
<?php

return [

    /**
         * Currency
         *
         * Control your currency settings. These will dictate
         * what currency products are sold in and how they are
         * formatted in the front-end.
    */
    
    'currency' => [
        'iso' => 'USD',
        'position' => 'left', // Options: left, right
        'separator' => '.',
    ],

];
```

Right now, Simple Commerce only supports a single currency. You can tell it which currency you wish to use and you can adjust the positioning of separators too.

### Notifications

```php
<?php

return [

    /**
         * Notifications
         *
         * Configure where we send your store's back
         * office notifications.
    */
    
    'notifications' => [
            'channel' => ['mail'],
    
            'mail_to' => 'admin@example.com',
            'slack_webhook' => '',
    ],

];
```

This is where you can control where you want your back of store notifications sent to. Currently the only options are `mail` and `slack`.

Depending on your option, you'll need to fill in other values, like your slack webhook or your to email.

### Other settings

```php
<?php

return [

    /**
         * Other Settings
         *
         * Some other settings for Simple Commerce.
         */
    
        'entered_with_tax' => false,
        'calculate_tax_from' => 'billingAddress', // Options: billingAddress, shippingAddress or businessAddress
        'shop_prices_with_tax' => true,
        'low_stock_counter' => 5,

];
```

There are some other things that you can configure if you want. Things like if your prices already include tax when entered, where you want to calculate tax from and when should we start notifying you about low stock.
