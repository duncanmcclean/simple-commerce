---
title: Dynamic Pricing
---

Depending on your use case, there may be situations where you need to use 'dynamic prices' for products depending on factors (eg. if customer is logged in, if customer is VIP, if it's a Friday, etc). Guess what - that's possible with Simple Commerce!

Essentially, the way it works is we provide a method for you to register a callback. Inside that callback you can do whatever decisioning you need to do to determine the price.

```php
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\Product;

SimpleCommerce::productPriceHook(function (Order $order, Product $product) {
    if (now()->isWeekend()) {
        return 1750;
    }

    return 1500;
});
```

Remember that you'll need to return the price as an integer. The above example returns `£17.50` or `£15.00` as prices.

An alternative method is also available for [variant products](/product-variants).

```php
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use DoubleThreeDigital\SimpleCommerce\Products\ProductVariant;

SimpleCommerce::productVariantPriceHook(function (Order $order, Product $product, ProductVariant $variant) {
    if (now()->isWeekend()) {
        return 1750;
    }

    return 1500;
});
```

> Note: These methods will not make any change to the price displayed to customers or stored in your products. They're only used when 'calculating' line items (eg. when an item is added to the cart, quantity changed, etc).
