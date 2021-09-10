---
title: Widgets
---

To help with managing small-medium sized stores, we've built a few widgets that should be helpful to see how your e-commerce store is doing.

## Configuring Widgets

You can display any of the built-in Simple Commerce widgets the same way you can with normal Statamic widgets. [See documentation](https://statamic.dev/widgets#updater).

For example, to display the Sales Widget, this is what your config would look like:

```php
'widgets' => [
	[
    	'type' => 'sales',
      	'width' => 50,
    ],
],
```

![Sales Widget](/img/simple-commerce/Sales-Widget.png)

## Sales widget

We've built-in a sales widget that allows staff to see the sales preformance of your store on a weekly, fortnightly or monthly basis.
