# Changelog

## Unreleased

## v2.0.24 (2020-10-06)

* [fix] Fix issue where gateways were not being booted

## v2.0.23 (2020-10-05)

* [new] Licensing statistics - so we can check how many sites are using Simple Commerce, what versions are in use and if they have valid licenses. (also introduced in [v2.1.2](https://github.com/doublethreedigital/simple-commerce/releases/tag/v2.1.2))

## v2.0.22 (2020-10-04)

* [new] You can now update the format of the customer titles.
* [fix] When updating a customer, sometimes the title would be removed... #311
* [fix] If updating a customer's name using `{{ sc:cart:update }}` tag, it wouldn't work because of some copy and pasting
* And added a bunch of customer related tests...

## v2.0.21 (2020-10-04)

* [fix] Fixed a bug that would happen if you try and get a customer that doesn't have a title or slug set.

## v2.0.20 (2020-10-04)

* [fix] Fixed issue where SC would presume a customer entry had a name on it, but if it didn't then stuff would break.

## v2.0.19 (2020-10-04)

* [fix] Fixed issue with customer data when being passed into Stripe Gateway, from #307

## v2.0.18 (2020-10-03)

* [fix] Fixed issues when creating/updating customers via the `{{ sc:cart:update }}` tag. #307

## v2.0.17 (2020-09-30)

* [fix] Fixed issue with decimals causing incorrect totals, when using coupons on an order #304

## v2.0.16 (2020-09-28)

* [new] Payment Method's are now saved for later, with Stripe Gateway #306

## v2.0.15 (2020-09-05)

* [fix] The money fieldtype will now default to 0 when nothing is entered instead of throwing an exception.
* [fix] Fixed issue where you couldn't remove the last item from the cart

## v2.0.14 (2020-08-29)

* [new] You can now register gateways on-demand with `SimpleCommerce::registerGateway(PayPalGateway::class, [])`
* [fix] Fixed issue where deleting a single cart item would clear the cart instead (again) #293
* [fix] Fixed issue when trying to submit checkout form without any customer information.

## v2.0.13 (2020-08-27)

* [fix] Fixes issue when adding to the cart when you've already deleted items from the cart. #293
* [fix] Generate a title and slug for customer if they don't already have one - fixes a type error #296
* [fix] Fixed issue when the `CheckoutController` tries to call the `PreCheckout` event on case sensitive systems. #294

## v2.0.12 (2020-08-26)

* [new] Brought back Order Statuses, you may want to run `php please simple-commerce:setup-command` to create the relevant taxonomies and terms.
* [new] You can now send customer metadata when using `{{ sc:cart:update }}` and `{{ sc:checkout }}` tags. #289
* [new] You can now toggle if Simple Commerce sends an `Order Confirmation` email to your customers after checkout. It's enabled by default. #288
* [new] Customer & Order information is now passed to Stripe when preparing payment intent #292
* [new] Brand new `php please simple-commerce:setup-command` command for setting up collections & taxonomies when installing Simple Commerce
* [fix] Fixed issue where adding items to cart would overwrite what is already there. #290

## v2.0.11 (2020-08-24)

* [fix] Fixed issue with ProductRepository not being bound properly because of a spelling mistake 🤦‍♂️ #287

## v2.0.10 (2020-08-22)

* [new] You can now specify the version of the Stripe API you want to use.
* [fix] Fixed issue caused when using a gateway that doesn't return anything from the prepare method.
* [fix] Fixed `checkAvailability` failing for shipping methods
* [fix] Fixed issue with completing cart without a customer being attached to the order.
* Changed version constraint of `statamic/cms` due to v3 release

## v2.0.9 (2020-08-18)

* [fix] Simple Commerce releases should now include built assets.
* [fix] Issue when entering value inside Money fieldtype without separator and it converts it to cents/penies
* [fix] Percentage coupons #281

## v2.0.8 (2020-08-17)

* [fix] Simple Commerce tags were broken after beta 44


## v2.0.7 (2020-08-17)

* [fix] Config, blueprint etc should no longer be overwritten on composer update
* Simple Commerce only supports PHP 7.4

## v2.0.6 (2020-08-14)

* [new] Refunds - somehow managed to ship without refunds but they're here now!
* [new] The output from prepare methods in gateways is now saved in the order so it can be used again in the gateway
* [break] Really small breaking change, inside the `{{ sc:gateways }}` loop, change `{{ config:* }}` to `{{ gateway-config:* }}` to grab gateway configuration values.
* [fix] Fixed initial state for the money fieldtype, should no longer show .
* [fix] Exceptions should now be thrown for when gateways don't exist or none is required at checkout
* [fix] `_redirect` should no longer be passed into cart when updating cart
* [fix] Fixed issue with coupon validation, where an error was being thrown as we were validation the wrong thing 🤦‍♂️ #276

## v2.0.5 (2020-08-11)

* [fix] Blueprints being re-published after every Simple Commerce update

## v2.0.4 (2020-08-10)

* [fix] Simple Commerce no longer relies on calebporzio/sushi for Currency and Country models
* [fix] Incorrect typehint causing issues when getting cart items
*[fix] Typos

## v2.0.3 (2020-08-06)

* [new] Introduced a `Product` facade, repository and related things...
* [fix] Fixed bug where you'd run into an error if you visit the cart/checkout when you have no cart in the session. Fixes #275
* [break] The `$request` variable is now passed in as a second parameter of the `purchase` method to a gateway.

## v2.0.2 (2020-08-01)

* [new] Introduced two new commands: `make:gateway` and `make:shipping-method`
* [fix] Use FormRequests for validating action endpoints
* [fix] Officially only supports PHP 7.4
* [break] Removed `/shipping-options` endpoints.
* [break] Removed need for SKUs, you can have them if you want them but they won't be saved in orders anymore.

## v2.0.1 (2020-07-31)

* [new] Added `PreCheckout` and `PostCheckout` events, triggered by the Checkout controller.

## 2.0.0 (2020-07-25)

* **Simple Commerce v2.0 has launched!**
