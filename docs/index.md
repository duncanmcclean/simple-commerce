---
title: Introduction
---

One. Two. Three. Test.

## One code snippet

```antlers
{{ collection:post }}
  {{ title }}
{{ /eollection:post }}
```

## Another code snippet

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
