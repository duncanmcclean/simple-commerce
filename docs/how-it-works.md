---
title: 'How it works'
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595078052
id: 84b28c73-3a04-478f-9447-68df026c44fe
is_documentation: true
nav_order: 3
---

Before getting started with your first Simple Commerce store, it's helpful to understand a bit of how it works under the hood.

## Content
Under the hood all of your products, orders and coupons are just normal Statamic collections and entries. Meaning that you can use them the same way as you can any of your other bits of contents.

For example, you can use [Statamic's collection tag](https://statamic.dev/tags/collection)

```
{{ collection:products }}
	<h2>{{ title }}</h2>
    <p>{{ description }}</p>
{{ /collection:products }}
```

You can also make use of Statamic's headless abilities, meaning you could run the front-end of your store on the JAMStack.

Each collection also has its own set of blueprints. When you install Simple Commerce, we create three collections for you. One for products, one for order and another for coupons.

You can edit these blueprints however you'd like. You could use Bard or replicator to create a mind blowing landing page or just add some asset fields to show what your product looks like. You've got the complete freedom of how you run your store.

## Templating
Antlers is the templating language built into Statamic. We ❤️ it. Simple Commerce comes with loads of [tags](/simple-commerce/tags) that help you integrate with Simple Commerce.

For example: to add an item to your cart, you can use a Simple Commerce tag which will create a form that points to one of Simple Commerce's endpoints. It means that something like this...

```
{{ sc:cart:addItem }}
    <input type="hidden" name="product" value="{{ id }}">
    <input type="hidden" name="quantity" value="1">
    <button class="button-primary">Add to Cart</button>
{{ /sc:cart:addItem }}
```

Would be output like this:

```
<form action="/!/simple-commerce/cart-items" method="post">
    <input type="hidden" name="product" value="84b28c73-3a04-478f-9447-68df026c44fe">
    <input type="hidden" name="quantity" value="1">
    <button class="button-primary">Add to Cart</button>
</form>
```

This allows you to design and develop your site however you'd like and you just sprinkle some Simple Commerce goodness whenever you need to talk to it. It works in a similar way to how things are done in Statamic itself.

> **Top Tip:** We recommend using Antlers when using Simple Commerce to get the best experience. We currently don't support other templating languages like Laravel Blade or Twig.

## Core Concepts
Last but not least, it's probably best you understand some of the core concepts. Without them, you'd be pretty lost.

* **Products** are the things that a customer can purchase. 
* **Orders** are pretty self explanitory. 
* **Carts** under the hood are the same as orders but we use it to describe orders that haven't been completed yet (so if a customer hasn't purchased the order)
* **Coupons** are a way of giving customers discounts on their cart. 
* **Customers** is a collection by Simple Commerce where all of your customers are stored. Each order has a customer assigned to it.