---
title: Use Simple Commerce with Static Caching
---

Static Caching is a really awesome feature of Statamic - taking advantage of it can really help to speed up your site!

Although, because e-commerce sites are dynamic (for example: the cart is different per user) you'll want to ignore those bits so they don't get cached.

Thankfully, you can wrap that code in Statamic's `nocache` tag. For example: you might want to do something like this for a line items counter in your site header:

```antlers
{{ nocache }}
    Cart ({{ sc:cart:count }} items)
{{ /nocache }}
```

Also, you'll want to do it for any other parts of your site that show cart information (like the cart/checkout pages). Just wrap the info in the tag and the job's a good 'n.

```antlers
{{ nocache }}
    {{ sc:cart }}
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                {{ items }}
                    <tr>
                        <td>
                            {{ product:title }}
                        </td>

                        <td>
                            {{ product:price }}
                        </td>

                        <td>
                            {{ sc:cart:updateItem :item="id" }}
                                <select
                                    name="quantity"
                                    onchange="this.parentElement.submit()"
                                >
                                    {{ loop from="1" to="5" }}
                                        <option
                                            value="{{ value }}"
                                            {{ if quantity == value }} selected {{ /if }}
                                        >
                                            {{ value }}
                                        </option>
                                    {{ /loop }}
                                </select>
                            {{ /sc:cart:updateItem }}
                        </td>

                        <td>{{ total }}</td>
                    </tr>
                {{ /items }}

                <tr>
                    <td></td>
                    <td></td>
                    <td>Subtotal</td>
                    <td>{{ items_total }}</td>
                </tr>
            </tbody>
        </table>
    {{ /sc:cart }}
{{ /nocache }}
```
