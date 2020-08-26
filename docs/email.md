---
title: Email
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595078000
id: 22499319-3c9f-4546-b792-4054d47d57fd
is_documentation: true
nav_order: 7
blueprint: documentation
---
By default, Simple Commmerce will automatically send emails to your customers when certain events happen. For example, Simple Commerce will send your customers an order confirmation email when a purchase has been completed.

## Configuration

```php
<?php

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
|
| Simple Commerce can automatically send notifications to customers after
| events occur in your store. eg. a cart being completed.
|
| Here's where you can toggle if certain notifications are enabled/disabled.
|
*/

'notifications' => [
    'cart_confirmation' => true,
],
```

In your Simple Commerce config (`config/simple-commerce.php`), you can toggle notifications on or off, depending on your preference. For example, you may not want Simple Commerce to send an order confirmation email because Stripe might send one to the customer instead.

## Customising email views

Simple Commerce uses [Laravel's markdown mail](https://laravel.com/docs/7.x/mail#markdown-mailables) feature, meaning we can use Blade views with markdown in them and it will be sent as an email.

If you'd like to customise the text used, Simple Commerce automatically publishes them to your `resources/views/vendor/simple-commerce`.
