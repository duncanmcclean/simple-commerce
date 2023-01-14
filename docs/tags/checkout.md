---
title: Checkout
---

This tag allows you to checkout the cart. Inside the tag, you can use any of the data from your cart. The `redirect` parameter is recommended so you can redirect the customer to a success page when they're order has been confirmed.

Like with the update cart tag, you can also pass information to the customer entry. Don't forget the `email` field though as it's required.

```antlers
{{ sc:checkout redirect="/thanks" }}
  {{ if is_paid }}
  <p>Checkout complete!</p>
  {{ else }}
    <input type="text" name="customer[name]" placeholder="Full Name">
    <input type="email" name="customer[email]" placeholder="Email">

    <input type="text" name="gift_note" placeholder="Gift Note">

    <select name="gateway">
      {{ sc:gateways }}
        <option value="{{ class }}">{{ name }}</option>
      {{ /sc:gateways }}
    </select>

    <!-- deal with your gateway stuff -->

    <button type="submit">Checkout</button>
  {{ /if }}
{{ /sc:checkout }}
```

If you're using an off-site gateway, like Mollie, you can learn about the checkout process, [over here](/gateways#offsite-gateways).

:::tip Hot Tip
I'd highly recommend disabling the button after the user submits the form to prevent them from submitting it multiple times.
:::

## Using a single gateway

If you're only needing to use a single gateway, instead of giving the user an option between gateways, you can force the user to use a specific gateway.

You simply need to specify the handle in the `{{ sc:gateways }}` tag.

```antlers
{{ sc:checkout redirect="/thanks" }}
  {{ if is_paid }}
  <p>Checkout complete!</p>
  {{ else }}
    <input type="text" name="customer[name]" placeholder="Full Name">
    <input type="email" name="customer[email]" placeholder="Email">

    <input type="text" name="gift_note" placeholder="Gift Note">

    {{ sc:gateways:stripe }}
        <!-- deal with your gateway stuff -->
    {{ /sc:gateways:stripe }}

    <button type="submit">Checkout</button>
  {{ /if }}
{{ /sc:checkout }}
```

## Successful Redirect

If you specify a `redirect` parameter on your Checkout form, Simple Commerce will retain the previous cart (eg. the one just checked out) when you use the `{{ sc:cart }}` tag.

This means, in the Checkout Success/Thanks page, you can use the cart tags like normal.

```
<h2>Thanks!</h2>
<p>Thanks for your order - {{ sc:cart:title }}.</p>
```

It's worth noting this same behavior works for off-site gateways as well.

:::note Note!
The cart information will only be available for 30 minutes after checking out. After which time, the page will start to use a fresh cart.
:::
