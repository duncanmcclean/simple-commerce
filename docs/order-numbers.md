---
title: Order Numbers
---

When an order is created, a unique order number will be generated. It'll often be formatted like so: `#1234`.

By default, order numbers will start at `#2000` and will continue endlessly. If you wish for order numbers to start at say, `#5000`, you can configure that in your `config/simple-commerce.php` config file.

```php
<?php

return [
	...

   /*
    |--------------------------------------------------------------------------
    | Order Number
    |--------------------------------------------------------------------------
    |
    | If you want to, you can change the minimum order number for your store. This won't
    | affect past orders, just ones in the future.
    |
    */

    'minimum_order_number' => 2000,
];
```
