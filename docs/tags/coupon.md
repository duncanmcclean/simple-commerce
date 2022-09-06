---
title: Coupons
---

### Cart's Coupon

This tag lets you get the data for the coupon, if the customer has redeemed one for the cart.

```antlers
{{ sc:coupon }}
  Currently redeemed: {{ slug }}
{{ /sc:coupon }}
```

### Check if coupon has been redeemed

This tag lets you check whether or not the customer has already redeemed a coupon.

```antlers
{{ if {sc:coupon:has} === true }}
  Coupon Discount: {{ sc:cart:couponTotal }}
{{ /if }}
```

### Redeem a coupoon

This tag will output a form, allowing your customers to enter a coupon code. On submitting the form, Simple Commerce will validate the coupon is valid & if it is, the coupon will be applied to the customers' cart.

```antlers
{{ sc:coupon:redeem }}
  <input type="text" name="code">
{{ /sc:coupon:redeem }}
```

### Remove a coupon

This tag allows you remove a redeemed coupon from the cart.

```antlers
{{ sc:coupon:remove }}
  <button type="submit">Remove coupon</button>
{{ /sc:coupon:remove }}
```
