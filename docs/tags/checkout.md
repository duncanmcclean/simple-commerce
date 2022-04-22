---
title: Checkout
---

This tag allows you to checkout the cart. Inside the tag, you can use any of the data from your cart. The `redirect` parameter is recommended so you can redirect the customer to a success page when they're order has been confirmed.

Like with the update cart tag, you can also pass information to the customer entry. Don't forget the `email` field though as it's required.

```antlers
{{ sc:checkout redirect="/thanks" }}
  {{ if is_paid }}
  <p>Checkout complete! <a href="{{ receipt_url }}">Download</a> your receipt.</p>
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

> **Hot Tip:** I'd highly recommend disabling the button after the user submits the form to prevent them from submitting it multiple times.
