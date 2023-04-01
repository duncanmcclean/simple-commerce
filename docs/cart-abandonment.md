---
title: Cart Abandonment
---

If a customer adds something to their cart but then leaves your site without coming back, you'll be left with an abandoned order in Simple Commerce. 

However, you may wish to delete orders that have been abandoned. Simple Commerce provides a command to delete abandoned carts/orders that were created more than 14 days ago.

```
php please sc:purge-cart-orders
```

You may setup the command so it's run on a regular schedule. Simply add it to your `app/Console/Kernel.php`

```php
$schedule->command('sc:purge-cart-orders')->daily();
```

For more documentation on scheduling, please [review the Laravel documentation](https://laravel.com/docs/master/scheduling#scheduling-artisan-commands).