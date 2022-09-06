---
title: Coupons
---

**Coupons** let you offer your customers fixed or percentage based discounts. To redeem, your customers simply need to enter a coupon code during the checkout process.

## Managing Coupons

![Update Coupon](/img/simple-commerce/coupon-publish-form.png)

You may configure coupons to only work if the cart meets certain conditions. For example: you might only want a coupon to be redeemable if their cart total is over £50.

In addition, you may also configure a coupon so it can only be redeemed a set number of times.

You can manage coupons in the Control Panel - under `Simple Commerce -> Coupons`.

## Templating

For full information on what's available to you, review the documentation of the [Coupon Tag](/tags/coupon).

### Redeeming coupons

This tag will output a form, allowing your customers to enter a coupon code. On submitting the form, Simple Commerce will validate the coupon is valid & if it is, the coupon will be applied to the customers' cart.

```antlers
{{ sc:coupon:redeem }}
  <input type="text" name="code">
{{ /sc:coupon:redeem }}
```
