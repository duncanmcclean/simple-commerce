---
title: Introduction
---

**Ecommerce is hard, don't do it alone.** Get back to doing what you love: building beautiful websites for your clients.

Simple Commerce is an ecommerce addon for Statamic, it's got everything you need to build a small-medium sized ecommerce store. And greatest of all it *feels* native to Statamic.

### Integrates perfectly with Statamic

Everyone loves Antlers, right? I sure do. Simple Commerce provides its own tags to let you add products to the cart, take payment etc. It’s almost magic.

```antlers
<h1 class="text-2xl">Your cart</h1>

{{ sc:cart }}
    <table>
        <tbody>
            {{ items }}
                <tr class="border-b border-gray-200">
                    <td class="text-sm px-2 py-4">{{ product:title }}</td>
                    <td class="text-sm px-2 py-4">{{ product:price }}</td>
                    <td class="text-sm px-2 py-4">{{ quantity }}</td>
                    <td class="text-sm px-2 py-4">{{ total }}</td>
                </tr>
            {{ /items }}
            <tr>
                <td class="text-sm px-2 py-4"></td>
                <td class="text-sm px-2 py-4"></td>
                <td class="text-sm px-2 py-4 font-semibold">Items Total</td>
                <td class="text-sm px-2 py-4">{{ items_total }}</td>
            </tr>
        </tbody>
    </table>
{{ /sc:cart }}
```

### Everything's just an entry

You know how you love flat files and entries for your content? Well, with Simple Commerce, your products, orders and coupons are all entries.

![Orders Collection](/img/simple-commerce/orders-collection.png)

### Flexible blueprints

There’s no limitations when it comes to blueprints. Just create the fields you wanna use and use them, it’s your site after all. No-one else should define your schema for you.

![Product Blueprint](/img/simple-commerce/product-blueprint.png)
