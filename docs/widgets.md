Simple Commerce provides a few widgets that you can add to the Dashboard of your Control Panel that displays key store information at a glance.

* `new_customers`
* `recent_orders`

You can add them to your Dashboard, by updating the list in your `cp.php` config file.

```php
<?php

return [

    'widgets' => [
            [
                'type' => 'recent_orders',
                'width' => 50,
            ],
            [
                'type' => 'new_customers',
                'width' => 50,
            ],
    ],
    
];
```
