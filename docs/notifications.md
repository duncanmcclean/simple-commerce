Simple Commerce sends out notifications whenever common events happen.

For example, we send out emails to customers when they have successfully went through the checkout flow. We also send out notifications to customers when the status of their order is updated and when an order of theirs has been refunded.

When sending to customers, Simple Commerce just uses the default email field in your `customers` table. It will send the email in the same way any Laravel email is sent, using the settings in your `config/mail.php` file. It's a good idea to change the from address in there to one from your domain and setup the email credentials in your `.env` file.

However, if you're dealing with back office notifications, you'll want to update the `notifications` array in your `config/simple-commerce.php` file.

```php
<?php

return [

    /**
     * Notifications
     *
     * Configure how we send your back of store notifications.
     */
    
    'notifications' => [
        'channel' => ['mail'],
    
        'mail_to' => 'admin@example.com',
        'slack_webhook' => '',
    ],

];
```

## Extending Notifications

If you wish to send notifications when other events happen, you can create your own addon, with a listener that fires a Laravel notification.

You can learn more about extending events [over here](./events.md) if you want.
