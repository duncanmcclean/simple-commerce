---
title: 'Core Concepts'
---

## Everything's just an entry

With Simple Commerce, all of your products, orders & customers are normal entries in Statamic.

This means you can take advantage of everything entries have to offer: front-end routing, multi-site localisations, fast search capabilities & more.

And, when your site grows, it's super simple to move your customers & orders [into the database](/database-orders).

## Integrates perfectly with Statamic

Everyone loves Antlers, am I right? Simple Commerce ships with a tone of Antler Tags so your customer can add products to their cart, redeem coupons and pay for their order.

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

If you'd rather [use Blade instead](/blade), you can also take advantage of our Antlers Tags inside Blade views.

## Powerful, but still simple

Simple Commerce has grown to support tones of features over the years.

However, to its core, Simple Commerce is still _simple_. There's no bloat that'll slow you down, features to workaround, you can use what you need and nothing more.
