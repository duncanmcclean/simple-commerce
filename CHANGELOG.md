# Changelog

## v7.6.3 (2025-01-14)

### What's fixed
* Remove coupon & reset coupon total when coupon is no longer valid #1196 by @duncanmcclean



## v7.6.2 (2024-12-09)

### What's fixed
* Fixed broken Assets fields in Product Variant options #1187 #1188 by @duncanmcclean



## v7.6.1 (2024-12-07)

### What's improved
* Improved performance by preventing unnecessary queries #1183 by @duncanmcclean

### What's fixed
* Fixed variant options not being added correctly #1185 #1186 by @duncanmcclean



## v7.6.0 (2024-11-29)

### What's new
* PHP 8.4 Support #1184 by @duncanmcclean



## v7.5.5 (2024-11-23)

### What's fixed
* Fixed tax rates with decimal places not being stored correctly #1154 by @duncanmcclean
* Fixed error when using imported fields in Product Variant option fields #1162 by @duncanmcclean
* Fixed augmentation issues with product variants #1178 by @duncanmcclean
* Fixed error when Stripe webhook was triggered for non-SC payments #1176 #1181 by @Web10-Joris

### Removed
* Removed telemetry by @duncanmcclean



## v7.5.4 (2024-10-28)

### What's fixed
* Fix variable being output in email template #1171 by @tobiv



## v7.5.3 (2024-10-12)

### What's fixed
* Fixed an issue with the `{{ sc:cart }}` tag when value is array #1165 #1170 by @darinlarimore
* Fixed an issue with shipping tax calculations in the Basic Tax Engine when `included_in_prices` is enabled #1168 by @rolinbos



## v7.5.2 (2024-10-01)

### What's fixed
* Made the `GeneratorCommand` class abstract, to prevent Statamic from autoloading it @duncanmcclean



## v7.5.1 (2024-09-20)

### What's fixed
* Ensure Runway blueprint are configured correctly when blueprints are stored in the database by @duncanmcclean



## v7.5.0 (2024-09-18)

### What's new
* Added `lineItemPriceHook` hook #1157 by @SteJW



## v7.4.7 (2024-09-06)

### What's fixed
* Fixed an issue where an exception would be thrown when redeeming invalid coupon #1153 by @jesseleite



## v7.4.6 (2024-09-04)

### What's fixed
* Differentiate subject from first order confirmation email #1152 by @jesseleite



## v7.4.5 (2024-08-31)

### What's fixed
* Workaround error when refunding Stripe payments
* Remove validation of Shipping Method fields #1147 by @duncanmcclean
* Worked around a weird issue when merging user data between flat-file and database users #1083 by @duncanmcclean



## v7.4.4 (2024-08-26)

### What's fixed
* Removed `tr_` prefix from Mollie webhook code #1148 by @Web10-Joris



## v7.4.3 (2024-08-21)

### What's fixed
* Mollie: Check for the payment ID under the `gateway` key too #1136 #1138 by @duncanmcclean



## v7.4.2 (2024-08-17)

### What's improved
* A warning is now thrown when the Runway config file already exists #1126 by @duncanmcclean

### What's fixed
* Fixed issue where fields were being wiped on users #1122 #1132 by @duncanmcclean
* Fixed issue where the `tax_category` field wasn't being added to variant products by @duncanmcclean
* Added back missing Region & Country fields to order bleprint by @duncanmcclean
* Updated `stripe/stripe-php` dependency #1125 #1127 by @david-lobo



## v7.4.1 (2024-08-05)

### What's fixed
* Ensure status log timestamps are always in UTC #1113 #1120 by @duncanmcclean
* Fixed empty `Address` objects being returned #1116 by @duncanmcclean
* Fixed empty Shipping Method field when storing orders in the database #1106 by @duncanmcclean
* Fixed issue where product variants weren't showing properly #1117 #1118 by @duncanmcclean
* Adjusted type hint of `GatewayData::data()` method #1111 by @duncanmcclean



## v7.4.0 (2024-07-20)

### What's new
* Floats can now be used in tax rates #1109 by @sdussaut




## v7.3.2 (2024-07-04)

### What's fixed
* Fixed issue where the value of the `tax_category` field was being saved incorrectly #1104 by @duncanmcclean
* When the order has no shipping tax, `Order@shippingTotalWithTax` should return zero.



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
