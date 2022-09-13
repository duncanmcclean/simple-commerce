---
title: 'Upgrade Guide: v3.x to v4.0'
---

## Overview

To get started with the upgrade process, follow the below steps:

**1.** In your `composer.json` file, update the `doublethreedigital/simple-commerce` version constraint:

```json
"doublethreedigital/simple-commerce": "^4.0"
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

**4.** Simple Commerce will have attempted upgrading some things for you (config changes, blueprint updates, etc). However, it's possible you will need to make some manual changes, along with some testing. **Please test before you push to production!**

## Changes

### Medium: Support for PHP 8.0 and Laravel 8 has been dropped

Simple Commerce has dropped support for both PHP 8.0 and Laravel 8. Leaving PHP 8.1 and Laravel 9 the only current versions supported.

These versions have been dropped to allow for Simple Commerce to take advantage of new PHP & Laravel features like proper Enums & Named Parameters.

To upgrade to Laravel 9, you should follow the steps outlined in the official [Upgrade Guide](https://laravel.com/docs/9.x/upgrade) or use a service like [Laravel Shift](https://laravelshift.com/upgrade-laravel-8-to-laravel-9) to automate most of the process for you.

### Medium: Changes to how coupons are stored

Previously, coupons were stored as collection entries. However, they've now been moved into their own Stache 'thing' to give Simple Commerce greater power over how they work.

All of your existing coupons should be migrated over automatically during the upgrade process. If this hasn't happened, follow these steps:

1. Create a new folder: `content/simple-commerce/coupons`
2. Copy all of your coupons entries to that new folder from `content/collections/coupons`
3. Change the file extension for the coupon files from `.md` to .`yaml`
4. Inside the coupon files, change `coupon_value` to `value` and remove `title`.
5. Also inside the coupon files, add the coupon code like so:

```yaml
code: your-coupon-code
```

6. Now, you can delete the Coupons collection & remove the `coupons` array from the `content` array inside your Simple Commerce config file.
7. Additonally, you will need to replace the fieldtype of the 'Coupon' field on your Orders blueprint. You should change it from the Entries fieldtype to the new, Coupons fieldtype.

If you're interacting with the Coupons API directly (eg. with PHP code), you shouldn't be required to make any changes. If you do end up needing to make some, please open an issue so this guide can be updated.

## Previous upgrade guides

-   [v2.2 to v2.3](/upgrade-guides/v2-2-to-v2-3)
-   [v2.3 to v2.4](/upgrade-guides/v2-3-to-v3-4)
-   [v2.4 to v3.0](/upgrade-guides/v2-4-to-v3-0)

---

[You may also view a diff of changes between v3.x and v4.0](https://github.com/duncanmcclean/simple-commerce/compare/3.x...4.x)
