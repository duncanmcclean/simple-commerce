---
title: Tags
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595078091
id: 6b491282-e792-431a-bc6a-912ee9b60edc
is_documentation: true
---
Simple Commerce provides a set of tags that'll help you to integrate with it in your templates.

## Cart

### Get the cart

Gets the customer's cart so you can get details from it. Say you wanted the id of the cart for some reason, here's how that would work.

```
{{ sc:cart }}
 <p>The ID of your cart is {{ id }}</p>
{{ /sc:cart }}
```

### Get cart items

Returns a loop of all the items in the customer's cart.

```
{{ sc:cart:items }}
  {{ quantity }}x {{ product:title }}
{{ /sc:cart:items }}
```

### Count

Gives you a count of how many items are in the customer's cart.

```
{{ sc:cart:count }}
```

### Total

Returns the grand total of the cart. You can actually do it either of these two ways:

```
{{ sc:cart:total }}
```

```
{{ sc:cart:grand_total }}
```

### Items Total

Returns the total of all items in the customer's cart.

```
{{ sc:cart:items_total }}
```

### Shipping Total

```
{{ sc:cart:shipping_total }}
```


### Tax Total

```
{{ sc:cart:tax_total }}
```

### Coupon Total

```
{{ sc:cart:coupon_total }}
```

### Add item to cart

```
{{ sc:cart:addItem }}
  <input type="hidden" name="product" value="{{ id }}">
  <input type="hidden" name="sku" value="{{ sku }}">
  <input type="number" name="quantity" value="2">
{{ /sc:cart:addItem }}
```

### Update cart item

```
{{ sc:cart:updateItem :item="id" }}
  <input type="number" name="quantity" value="2">
{{ /sc:cart:updateItem }}
```

### Remove cart item

```
{{ sc:cart:removeItem :item="id" }}
  <button type="submit">Remove item from cart</button>
{{ /sc:cart:removeItem }}
```

### Update cart

### Empty cart

## Checkout

TODO

```
{{ sc:checkout }}

{{ /sc:checkout }}
```

## Coupons

### Redeem coupon

### Remove coupon

## Customer

### Get customer


### Update customer

### Get customer's orders

### Get order by customer

## Gateways

### All gateways

### Get a gateway

## Shipping

### Get shipping options

### Choose a shipping option

## Get countries

## Get currencies