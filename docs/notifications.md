---
title: Email Notifications
---

Email notifications are essential for keeping customers updated in the progress of their orders.

Simple Commerce uses [Laravel Notifications](https://laravel.com/docs/master/notifications) to power email notifications sent to your customers.

## Configuration

```php
// config/simple-commerce.php

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
|
| Simple Commerce can automatically send notifications after events occur in your store.
| eg. a cart being completed.
|
| Here's where you can toggle if certain notifications are enabled/disabled.
|
| https://simple-commerce.duncanmcclean.com/notifications
|
*/

'notifications' => [
    'order_paid' => [
        \DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderPaid::class => [
            'to' => 'customer',
        ],
        \DuncanMcClean\SimpleCommerce\Notifications\BackOfficeOrderPaid::class => [
            'to' => 'cj.cregg@whitehouse.gov',
        ],
    ],

   // ...
],
```

Inside the `notifications` array, you can listen to events dispatched by Simple Commerce.

Then, for each event you're listening to, you may have one or more notifications you wish to be triggered when the event is dispatched.

As part of configuring notification classes, you should also configure where the notifications should be sent (the `to` parameter).

You can either provide `customer`, for the email to be sent to the associated customer, Antlers code to evaluate a field from the order data or just hard-code a specific email address.

### Available events

You may listen to any of the following events:

-   `order_cart`
-   `order_placed`
-   `order_dispatched`
-   `order_cancelled`
-   `order_unpaid`
-   `order_paid`
-   `order_refunded`
-   `order_payment_failed`
-   `stock_running_low`
-   `stock_run_out`

## Custom Notifications

If you need to make changes to the built-in email notifications, the best way to do that is by providing a custom notification class.

Here's a real quick rundown of how to generate your own notification class and set it up with Simple Commerce.

1. Generate a notification

```bash
php artisan make:notification OrderPaidNotification
```

2. In your `simple-commerce.php` config file, switch out the previous class for your new one.

```php
'notifications' => [
    'order_paid' => [
        \App\Notifications\OrderPaidNotification::class   => ['to' => 'customer'],
    ],
],
```

3. Copy over pretty much everything from [the default notification](https://github.com/duncanmcclean/simple-commerce/blob/3.x/src/Notifications/CustomerOrderPaid.php#L15) we provide and paste it into your new notification class.

4. Make whatever changes you need to make!

## Customising email templates

Simple Commerce doesn't make any changes to the email template that ships with Laravel.

It's pretty generic but you may wish to switch it up a bit when working on a bespoke e-commerce site.

All you need to do is publish Laravel's notification views:

```bash
php artisan vendor:publish --tag=laravel-notifications
```

Then, the email views will be published into the `resources/views/vendor` directory.

## Testing emails

While you're in development, you'll probably want to test out your emails without faffing around with _real_ email.

Tools like [HELO](https://a.paddle.com/v2/click/103161/130785?link=2990) and [Mailtrap](https://mailtrap.io/) let you catch all emails coming out of your site during development.

![HELO Screenshot](/img/simple-commerce/helo-screenshot.png)

*Disclaimer:* The HELO link above is an affiliate link.
