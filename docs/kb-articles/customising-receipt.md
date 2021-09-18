---
title: Customise the receipt view
---

Simple Commerce provides a really basic receipt view that is used when sending the default [order confirmation notification](/notifications) to customers.

If you need to make changes, like changing the address, the design or anything else, you can publish the view. 

To publish the view, run the following command:

```
php artisan vendor:publish --tag="simple-commerce-views"
```

The receipt's Antlers view will be published into your `resources/views/vendor/simple-commerce` directory. You can make any required changes in there and they'll be reflected wherever it's used.