---
title: Calculator
---

At the heart of Simple Commerce is the Calculator. The calculator decides the totals of line items, tax total, shipping total etc.

## Recalculating the cart

If you need to recalculate the cart yourself, maybe from an addon or some custom code you're writing, you can use the `calculateTotals` available on an Order.

```php
Order::find('abc-123')->calculateTotals();
```

## Extending the calculator

There's some situations where you'd want to extend the calculator. Maybe to add your own discounting functionality or hook into some external API for tax etc.

First, you'll need to make your own class, which implements the `Calculator` interface and likley also extends the base calculator.

```php
<?php

namespace App;

use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator as Contract;
use DoubleThreeDigital\SimpleCommerce\Orders\Calculator as BaseCalculator;

class Calculator extends BaseCalculator implements Contract
{
    public function calculateLineItem(array $data, array $lineItem): array
    {
        // Everything is £5
        $lineItem['total'] = 500;

        return [
            'data' => $data,
            'lineItem' => $lineItem,
        ];
    }
}
```

Additionally, you will need to bind your custom class to Laravel's service container, which you can do in a service provider (`app/Providers/AppServiceProvider.php`).

```php
// app/Providers/AppServiceProvider.php

use App\Calculator;
use Statamic\Statamic;

...

public function register()
{
	Statamic::repository(
        \DoubleThreeDigital\SimpleCommerce\Contracts\Calculator::class,
        Calculator::class
    );
}
```
