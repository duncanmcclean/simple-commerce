---
title: Stock
---

## Enabling

### Standard Product

To start using the stock functionality built into Simple Commerce, you'll need to add an [`Integer` field](https://statamic.dev/fieldtypes/integer#content), with the handle of `stock` to your product blueprint.

After that, you can populate your products with the correct amount of stock. 

Then, whenever a customer purchases that product, the stock counter will be decreased.

### Variant Product

To start using the stock functionality with variant products, you'll need to add an 'option field' to your Product Variants field, with the handle of `stock` and using the [Integer fieldtype](<(https://statamic.dev/fieldtypes/integer#content)>).

![Stock Variant Options](/img/simple-commerce/variant-options-stock.jpg)

After that, head into your products and populate your variants with the correct amount of stock. 

Then, whenever a customer purchases that variant, the stock counter will decreased.

## Events

Simple Commerce will fire events when either your stock is [running low](/extending/events#stockrunninglow) or if your [stock has ran out](/extending/events#stockrunout) for one of your products.

You can configure the threshold for when you want to start dispatching the `StockRunningLow` event in your `simple-commerce.php` config.

```php
// config/simple-commerce.php

/*
 |--------------------------------------------------------------------------
 | Stock Running Low
 |--------------------------------------------------------------------------
 |
 | Simple Commerce can be configured to emit events when stock is running low for
 | products. Here is where you can configure the threshold when we start sending
 | those notifications.
 |
*/

'low_stock_threshold' => 10,
```
