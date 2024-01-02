---
title: Control Panel
---

## Control Panel Navigation

Once installed, you'll see a "Simple Commerce" section in the Control Panel navigation. It provides easy access to Orders, Products, Coupons, etc.

![Simple Commerce section in Control Panel nav](/img/simple-commerce/cp-nav-section.png)

If you want to hide pages from the Control Panel navigation, you can use the [Nav Customizer](https://statamic.dev/customizing-the-cp-nav) .

## Widgets

Simple Commerce comes with a few helpful [widgets](https://statamic.dev/widgets) you can add to your site's dashboard.

### Orders Chart

![Screenshot of the Recent Orders widget](/img/simple-commerce/orders-chart-widget.png)

The Orders Chart widget displays a line chart of paid orders over the last 30 days.

```php
// config/statamic/cp.php

'widgets' => [
    [ // [tl! highlight:3]
        'type' => 'orders_chart',
        'width' => 100,
    ],
],
```

### Recent Orders

![Screenshot of the Recent Orders widget](/img/simple-commerce/recent-orders-widget.png)

The Recent Orders widget displays a list of the site's recent orders.

```php
// config/statamic/cp.php

'widgets' => [
    [ // [tl! highlight:4]
        'type' => 'recent_orders',
        'width' => 50,
        'limit' => 5,
    ],
],
```

By default, it'll only show 5 orders but you can adjust the `limit` however you'd like.

### Top Customers

![Screenshot of the Top Customers widget](/img/simple-commerce/top-customers-widget.png)

The Top Customers widget displays a list of the site's top customers (those with the most orders).

```php
// config/statamic/cp.php

'widgets' => [
    [ // [tl! highlight:4]
        'type' => 'top_customers',
        'width' => 50,
        'limit' => 5,
    ],
],
```

By default, it'll only show 5 customers but you can adjust the `limit` however you'd like.


### Low Stock Products

![Screenshot of the Low Stock Products widget](/img/simple-commerce/low-stock-products-widget.png)

The Low Stock Products widget displays a list of products with low stock.

```php
// config/statamic/cp.php

'widgets' => [
    [ // [tl! highlight:4]
        'type' => 'low_stock_products',
        'width' => 50,
        'limit' => 5,
    ],
],
```

By default, it'll only show 5 products but you can adjust the `limit` however you'd like.
