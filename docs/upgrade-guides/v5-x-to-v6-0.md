---
title: 'Upgrade Guide: v5.x to v6.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple versions at once (eg. from v4 to v6). Please upgrade one step at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** In your `composer.json` file, update the `doublethreedigital/simple-commerce` version constraint:

```json
"doublethreedigital/simple-commerce": "^6.0"
```

**2.** Then run:

```
composer update doublethreedigital/simple-commerce --with-dependencies
```

**3.** You may also want to clear your route & view caches:

```
php artisan route:clear
php artisan view:clear
```

**4.** Simple Commerce will have attempted upgrading some things for you (like config files, blueprints, etc). However, it's possible you'll need to make some manual changes. Please review this guide for information on changes which may effect your site.

**Please test locally before deploying to production!**

## Changes

### Medium: The `all` method on repositories has changed

If you have any custom code which calls `Order::all()`, `Product::all()` or `Customer::all()`, you may need to adjust your code.

The `all` method on these repositories now returns a `Collection` of `Order`/`Product`/`Customer` objects, rather than returning an array of `Entry` or Eloquent model objects.

This saves you needing to `find` the order/product/customer to use any of Simple Commerce's helper methods.

## Previous upgrade guides

-   [v2.2 to v2.3](/upgrade-guides/v2-2-to-v2-3)
-   [v2.3 to v2.4](/upgrade-guides/v2-3-to-v3-4)
-   [v2.4 to v3.0](/upgrade-guides/v2-4-to-v3-0)
-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)

---

[You may also view a diff of changes between v5.x and v6.0](https://github.com/duncanmcclean/simple-commerce/compare/5.x...main)
