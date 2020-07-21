---
title: Tags
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595078091
id: 6b491282-e792-431a-bc6a-912ee9b60edc
is_documentation: true
nav_order: 5
---
Simple Commerce provides a bunch of tags to help you to integrate it inside your templates. 

## Cart

### The whole cart
Gets the customer's cart so you can get details from it. Say you wanted the id of the cart for some reason, here's how that would work.

```
{{ sc:cart }}
 <p>The ID of your cart is {{ id }}</p>
{{ /sc:cart }}
```

### Cart Check
This tag allows you to check whether or not the customer currently has a cart attached to their session, it returns a boolean.

```
{{ if {sc:cart:has} === true }}
  ...
{{ /if }}
```

### Cart Items
Returns a loop of all the items in the customer's cart.

```
{{ sc:cart:items }}
  {{ quantity }}x {{ product:title }}
{{ /sc:cart:items }}
```

### Items Count
Gives you a count of how many items are in the customer's cart.

```
{{ sc:cart:count }}
```

### Totals
**Grand Total**
Returns the total of all the other totals. In fact, there's two ways of doing it.

```
This... {{ sc:cart:total }}

Does exactly the same thing as this... {{ sc:cart:grand_total }}
```

**Items Total**
Returns the total of every item in the cart.

```
{{ sc:cart:items_total }}
```

**Shipping Total**
Returns the shipping total of the cart.

```
{{ sc:cart:shipping_total }}
```

**Tax Total**
Returns the tax total of the cart.

```
{{ sc:cart:tax_total }}
```

**Coupon Total**
Returns the total of the coupons in the cart.

```
{{ sc:cart:coupon_total }}
```

### Add Cart Item
This tag allows you to add an item to your cart.

```
{{ sc:cart:addItem }}
  <input type="hidden" name="product" value="{{ id }}">
  <input type="hidden" name="sku" value="{{ sku }}">
  <input type="number" name="quantity" value="2">
{{ /sc:cart:addItem }}
```

### Update Cart Item
This tag allows you to update the items in the cart.

```
{{ sc:cart:updateItem :item="id" }}
  <input type="number" name="quantity" value="2">
{{ /sc:cart:updateItem }}
```

### Remove Cart Item
This tag allows you to remove an existing item from the cart.

```
{{ sc:cart:removeItem :item="id" }}
  <button type="submit">Remove item from cart</button>
{{ /sc:cart:removeItem }}
```

### Update cart
This tag allows you to update data in your cart.

```
{{ sc:cart:update }}
  <input type="text" name="name">
  <input type="text" name="email">
{{ /sc:cart:update }}
```

### Empty cart
This tag removes all the items in the cart. 

```
{{ sc:cart:empty }}
  ...
{{ /sc:cart:empty }}
```

## Checkout
This tag allows you to checkout the cart. Inside the tag, you can use any of the data from your cart.

```
{{ sc:checkout }}
  {{ if is_paid }}
  <p>Checkout complete! <a href="{{ receipt_url }}">Download</a> your receipt.</p>
  {{ else }}
    <input type="text" name="name" placeholder="Full Name" value="{{ old:name }}">
    <input type="email" name="email" placeholder="Email" value="{{ old:email }}">

    <select name="gateway">
      {{ simple-commerce:gateways }}
        <option value="{{ class }}">{{ name }}</option>
      {{ /simple-commerce:gateways }}
    </select>

    <!-- deal with your gateway stuff -->

    <button type="submit">Checkout</button>
  {{ /if }}
{{ /sc:checkout }}
```

## Coupons

### Cart's Coupon
This tag lets you get the data for the coupon, if the customer has redeemed one for the cart.

```
{{ sc:coupon }}
  Current coupon: {{ slug }}
{{ /sc:coupon }}
```

### Check if coupon has been redeemed
This tag lets you check whether or not the customer has already redeemed a coupon.

```
{{ if {sc:coupon:has} === true }}
  You've redeemed a coupon.
{{ /if }}
```

### Redeem a coupoon
This tag lets you redeem a coupon.

```
{{ sc:cart:redeem }}
  <input type="text" name="code">
{{ /sc:cart:redeem }}
```

### Remove a coupon
This tag allows you remove a redeemed coupon from the cart.

```
{{ sc:cart:remove }}
  <button type="submit">Remove coupon</button>
{{ /sc:cart:remove }}
```

## Customer

### Get current customer
This tag will get the currently logged in user's data. Although, it basically does the same thing as the `{{ user }}` tag.

```
{{ sc:customer }}
  Your name is {{ name }} and my email is {{ email }}.
{{ /sc:customer }}
```

### Update current customer
This tag allows you to update the currently logged in user.

```
{{ sc:customer:update }}
  <input type="text" name="name">
{{ /sc:customer:update }}
```

### Get customer's orders
This tag allows you to loop through orders by the currently logged in customer.

```
{{ sc:customer:orders }}
  {{ title }} - {{ grand_total }}
{{ /sc:customer:orders }}
```

### Get order by customer
This tag allows you to get an order by the currently logged in customer.

```
{{ sc:customer:order id="84b28c73-3a04-478f-9447-68df026c44fe" }}
  {{ title }} - {{ grand_total }}
{{ /sc:customer:order }}
```

## Gateways

### All gateways
This tag returns a loop of the gateways setup for your store.

```
{{ sc:gateways }}
  {{ name }}
{{ /sc:gateways }}
```

### Get a gateway
This tag lets you get a particular gateway and its information, where `stripe` is the handle of the gateway.

```
{{ sc:gateways:stripe }}
  {{ name }}
{{ /sc:gateways:stripe }}
```

## Shipping
TODO - this still needs to be built

## Get countries
This tag lets you loop through countries.

```
{{ sc:countries }}
  {{ name }}
{{ /sc:countries }}
```

## Get currencies
This tag lets you loop through currencies.

```
{{ sc:currencies }}
  {{ name }} - {{ symbol }}
{{ /sc:currencies }}
```

## And a few things...
If you're dealing with forms built by a Simple Commerce tag, there's a few cool things you can do.

Firstly, you can add a `redirect` param so you can redirect your user once they submit the form (and the validation is successful). In this example, the form will redirect to `/cart`.

```
{{ sc:cart:addItem redirect="/cart" }}
    <input type="hidden" name="product" value="{{ id }}">
    <input type="hidden" name="sku" value="test-1">  
    <input type="hidden" name="quantity" value="1">
    <button class="button-primary">Add to Cart</button>
{{ /sc:cart:addItem }}
```

Also, if you don't like prefixing `sc` in the tags, you can use `simple-commerce` instead.