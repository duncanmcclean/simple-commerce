---
title: Notifications
---

Notifications are a must for any e-commerce store, especially email notifications. Simple Commerce hooks into Laravel's [Notification's feature](https://laravel.com/docs/master/notifications) to send notifications.

With this, you can send notifications via email, via SMS or even in real time if you use the `broadcast` driver.

## Configuration

```php
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
        \DoubleThreeDigital\SimpleCommerce\Notifications\CustomerOrderPaid::class => [
            'to' => 'customer',
        ],
        \DoubleThreeDigital\SimpleCommerce\Notifications\BackOfficeOrderPaid::class => [
            'to' => 'duncan@example.com',
        ],
    ],

   // ...
],
```

Inside the `notifications` array, you can list the event you would like to send notifications on, then you can provide another array with each of the notifications you'd like to send when that event happens.

You may also configure who you wish to send each notification to. If you'd like to send the notification to a customer, send it to the `customer`. Otherwise, you can simply send it to an email address.

### Available events

You may listen to any of the following events:

- `order_paid`
- `order_payment_failed`
- `order_shipped`
- `stock_running_low`
- `stock_run_out`

## Using a custom notification

If you want to change the text used in the notification or maybe provide more information about the order, using a custom notification is the best way to do that.

Here's a quick rundown of how to generate your own notification and configure it to be used by Simple Commerce.

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

3. Copy over pretty much everything from [the default notification](https://github.com/doublethreedigital/simple-commerce/blob/2.3/src/Notifications/CustomerOrderPaid.php#L15) we provide and paste it into your new notification class.

4. Make whatever changes you need to make!

## Customising the email views

Simple Commerce uses the default email views provided by Laravel. You may publish these views to your `resources/views/vendor` directory using the following command:

```bash
php artisan vendor:publish --tag=laravel-notifications
```

## Testing emails

While you're in development, you may need to test your emails without faffing around with _real_ emails.

For this, I'd recommend using a tool like [HELO](https://a.paddle.com/v2/click/103161/130785?link=2990) or [Mailtrap](https://mailtrap.io/) which let you view emails during development.

Disclaimer: The HELO link is a 30% affiliate link.
