---
title: Saving Line Items without recalculating
---

Usually, whenever you add/remove/update line items, Simple Commerce will automatically recalculate your totals for you.

There are some cases where the totals continuously being recalculated can be a pain, so there's a way to turn it off while doing things...

```php
$this->withoutRecalculating(function () use ($calculate, $request) {
    $this->clearLineItems();

    collect($request->lineItems)
        ->each(function ($lineItem) {
            $this->addLineItem($lineItem);
        });
});
```
