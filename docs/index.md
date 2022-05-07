---
title: Introduction
table_of_contents: false
slider:
  - /img/promo/simple-commerce/back-office-overview.png
  - /img/promo/simple-commerce/everything-is-an-entry.png
  - /img/promo/simple-commerce/payment-gateways.png
  - /img/promo/simple-commerce/product-variants.png
  - /img/promo/simple-commerce/coupons.png
  - /img/promo/simple-commerce/shipping.png
  - /img/promo/simple-commerce/multisite.png
---

**Ecommerce is hard. don't do it alone!** Get back to doing what you love: building beautiful websites for your clients.

imple Commerce is a simple, yet powerful e-commerce addon for Statamic. You have complete control over the content structure and front-end of your site.

### Everything's just an entry

Stay with what you love - Statamic entries. With Simple Commerce, all of your products, orders, customers & coupons are Statamic entries. Giving you the flexibility you need to build bespoke e-commerce sites for your clients.

And, when your site grows, it's easy to [move your orders & customers](/database-orders) into a traditional database.

### Integrates perfectly with Statamic

Everyone loves [Antlers](https://statamic.dev/antlers), right? I sure do. Simple Commerce provides its own Antlers tags to let you add products to the cart, take payment etc. It’s almost magic 🪄.

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

### Payment Gateways

Out-of-the-box, Simple Commerce ships with support for three of the big [payment gateways](/gateways): Stripe, PayPal and Mollie. Use whichever one you need, or if you need something else: it's easy to build one.

```php
'gateways' => [
	\DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\StripeGateway::class => [
    	'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
],
```

### Getting Started

To get started with Simple Commerce, follow the [Installation Guide](/installation). It'll walk you through the process of getting up and running!

And, if you have any questions, [send me an email](mailto:help@doublethree.digital) and I'll be more than happy to help!
