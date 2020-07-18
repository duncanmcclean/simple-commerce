# Cart

::: v-pre

## Items

You can use this tag to get all the line items inside the customer's cart.

```html
<ul>
    {{ cart:items }}
        <li>{{ product:title }} ({{ variant:sku }}) - {{ total }}</li>
    {{ /cart:items }}
</ul>
```

> `{{ cart }}` on its own is just an alias of `{{ cart:items }}`

## Count

You can use this tag to get a count of how many line items a customer has in their cart.

```html
<p>You have {{ cart:count }} items in your cart.</p>
```

## Total

This tag can be used to get a cart total. There are a few totals you can get:

* item total
* shipping total
* tax total
* coupon total
* grand total

And you can get them like this:

```html
<ul>
    <li><strong>Item Total:</strong> {{ cart:total items='true' }}</li>
    <li><strong>Shipping Total:</strong> {{ cart:total shipping='true' }}</li>
    <li><strong>Tax Total:</strong> {{ cart:total tax='true' }}</li>
    <li><strong>Coupon Total:</strong> {{ cart:total coupon='true' }}</li>
    <li><strong>Grand Total:</strong> {{ cart:total }}</li>
</ul>
```