---
title: Variables
---

## Available variables

### Orders

#### Receipt URL

Say you're on an Order History page and looping through the customers' previous orders and you need a link to the orders' receipt, here you can do that.

```html
{{ sc:customer:orders }}
<h2>{{ title }}</h2>
<a href="{{ receipt_url }}" target="_blank">Download receipt</a>
{{ /sc:customer:orders }}
```

You can also do this anywhere else you have an order, even if you are looping through the orders in a normal Statamic collection tag.

## How they work

Sadly, it's not that simple to just 'add variables' to an entry.

Behind the scenes, each of the variables are basically their own 'Hidden' fieldtypes. And then, when Statamic gets the blueprint for one of your SC collections (eg. orders, products), it checks in with Simple Commerce in case it wants to add any variables of its own to the blueprint. It's at this step, we add our special fieldtypes.

Then, when you have an [augmented](https://statamic.dev/extending/augmentation) version of an entry, Simple Commerce's hidden variables will be available.
