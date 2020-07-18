# Notifications

Simple Commerce allows you to easily notify customers and back office staff when things happen in your store, like new orders, variants running out of stock etc.

Out of the box, it can send notifications through e-mail and Slack (you'll need to `composer require laravel/slack-notification-channel` for slack to work).

## Configuration

```php
/**
	* Notifications
	*
	* Configure what notifications we send and who we 
	* send them to.
*/

'notifications' => [
	'notifications' => [
		\DoubleThreeDigital\SimpleCommerce\Events\BackOffice\NewOrder::class => ['mail'],
		\DoubleThreeDigital\SimpleCommerce\Events\BackOffice\VariantOutOfStock::class => ['mail'],
		\DoubleThreeDigital\SimpleCommerce\Events\BackOffice\VariantStockRunningLow::class => ['mail'],
		\DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded::class => ['mail'],
		\DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated::class => ['mail'],
		\DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful::class => ['mail'],
	],

	'mail' => [
		'to' => 'hello@example.com',

		'from' => [
			'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
			'name' => env('MAIL_FROM_NAME', 'Example'),
		],
	],

	'slack' => [
		'webhook_url' => '',
	],
],
```

The `notifications` array contains each of the Simple Commerce provided notifications, the value of each of the items is the channels you wish the notification to be sent to, `mail` and `slack` are the options.

`mail` lets you setup the to address and from address you wish emails to be sent with. The `to` address will only be used for back-office notifications, meaning notifications that aren't sent to a customer.

The `slack` array lets you define the URL of the Slack webhook your notifications should be sent to.

## Customising the notifications

Right now, you can't customise the text of the notifications that Simple Commerce sends. However, you can customise the views used for any notifications sent by Laravel, you can publish it by running the below command.

```
php artisan vendor:publish --tag=laravel-notifications
```

If you really want to customise the text used inside the notifications, you need to disable the ones provided by Simple Commerce, simply by commenting out each of the class names in the `notifications` array, and you can register your own by listening to the correct events. [Learn more about events](./extending/events).