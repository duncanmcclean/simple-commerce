---
title: Coupons
---

Coupons let you offer customers fixed or percentage based discounts. 

To redeem, your customers simply need to enter a valid coupon code into a `{{ sc:coupon:redeem }}` form.

## Managing Coupons

![Update Coupon](/img/simple-commerce/coupon-publish-form.png)

You can manage coupons in the Control Panel - under `Simple Commerce -> Coupons`.

### Rules

Beyond creating a coupon & setting the discount amount, you may also configure a set of additional rules to check if the customer's cart qualifies for the discount.

* **Maximum uses:** this setting allows you to limit a coupon to only be redeemed a certain number of times. 
* **Minimum cart value:** this setting allows you to set a minimum amount for which carts should reach in order to redeem the coupon.
* **Products:** this setting allows you to select products which limit the coupon to only be redeemed when any of the selected products are present.
* **Expires at:** this setting allows you to configure an expiry date. Customers will be unable to redeem the coupon after the expiry date has passed.

## Templating

### Redeem a coupon

You may use the `{{ s:coupon:redeem }}` tag to let customers redeem a coupon.

You should include a `code` input inside the form tag.

```antlers
{{ sc:coupon:redeem }}
  <input type="text" name="code">
{{ /sc:coupon:redeem }}
```

### More information

For more information about the available Coupon tags, please review the [`{{ sc:coupon }}` tag documentation](/tags/coupon).