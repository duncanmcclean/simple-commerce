# Changelog

## v7.3.1 (2024-06-13)

### What's fixed
* Fixed the Low Stock Products widget with Variant Products #1100 #1101 by @duncanmcclean



## v7.3.0 (2024-06-08)

### What's new
* The `CouponRedeemed` event now includes the order #1091 by @sbrow
* You can now set the expiry date for the cookie cart driver #1089 by @Web10-Joris

### What's fixed
* Added additional "has cart" checks to the cart tag, to prevent empty carts #1096 by @duncanmcclean
* Fixed the `{{ sc:cart:shipping_total_with_tax }}` tag when the tax rate has "Price includes tax" enabled #1095 by @duncanmcclean



## v7.2.1 (2024-05-31)

### What's fixed
* Fixed error when querying customers stored as database users #1082 #1088 by @duncanmcclean
* Fixed error when saving user customer data #1083 #1087 by @duncanmcclean
* Fixed `{{ sc:customer:order }}` tag #1081 #1086 by @duncanmcclean
* Prevented `preload` method from being called when augmenting product variants #1076 #1085 by @duncanmcclean
* Fixed broken link to the Statamic 5 upgrade guide in the docs #1080 by @Afan417



## v7.2.0 (2024-05-21)

### What's improved

* Dark Mode support #1077 by @duncanmcclean



## v7.1.0 (2024-05-18)

### What's new
* Both v3 and v4 of `stillat/proteus` are now supported #1074 by @duncanmcclean



## v7.0.1 (2024-05-11)

### What's fixed
* Fixed Stripe Payment Intent not being saved to order gateway data #1071 #1072 by @duncanmcclean



## v7.0.0 (2024-05-09)

### Read First 👀
Be sure to read the [Upgrade Guide](https://simple-commerce.duncanmcclean.com/upgrade-guides/v6-to-v7) first as manual changes may be necessary.

### What's new

* Statamic 5 support #1039 by @duncanmcclean

### What's changed

* Dropped PHP 8.1 support
* Dropped Statamic 4 support
