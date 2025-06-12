---
title: 'Upgrade Guide: v7.x to v8.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple versions at once (eg. from v7 to v9). Please upgrade one version at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** In your `composer.json` file, change the `duncanmcclean/simple-commerce` version constraint to `^8.0`:

```json
"duncanmcclean/simple-commerce": "^8.0"
```

**2.** Then run:

```
composer update duncanmcclean/simple-commerce --with-dependencies
```

**3.** Next, please ensure you have cleared the route and view caches:

```
php artisan route:clear
php artisan view:clear
```

**4.** You're now running Simple Commerce v8. Please review this upgrade guide for information on changes which may affect your project.

**Please test your project locally before deploying to production!**

## Changes

### Statamic support
Simple Commerce v8 requires Statamic 6. Please review the [Statamic 6 upgrade guide](https://statamic.dev/upgrade-guide/5-to-6) before upgrading.

### PHP support
**Affects apps using PHP 8.2**

Simple Commerce v8 now requires PHP 8.3 or later. We highly recommend upgrading to PHP 8.4, if possible.

## Migrating to Cargo

Simple Commerce has been replaced by [Cargo](https://builtwithcargo.dev), the natural evolution of Simple Commerce. It takes everything you love about Simple Commerce and makes it better in every possible way.

As v8 will be the last major version of Simple Commerce, you should investigate migrating to Cargo. You can find more information on the migration process over on the [Cargo documentation](https://builtwithcargo.dev/docs/migrating-from-simple-commerce).

## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)
-   [v5.x to v6.0](/upgrade-guides/v5-x-to-v6-0)
-   [v6.x to v7.0](/upgrade-guides/v6-to-v7)

---

[You may also view a diff of changes between v7.x and v8.0](https://github.com/duncanmcclean/simple-commerce/compare/7.x...8.x)
