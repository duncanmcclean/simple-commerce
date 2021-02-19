# Changelog

## Unreleased

## v2.1.36 (2021-02-19)

* [fix] Tax amounts should no longer be off. Was previously using the wrong formula.

## v2.1.35 (2021-01-30)

* [fix] Actually use the new format when adding items to the cart
* [fix] Fixed issue when clearing the cart

## v2.1.34 (2021-01-30)

* [new] Updated the default order blueprint
* [new] Added a new 'Product Variant' fieldtype to select a single variant

## v2.1.33 (2021-01-27)

* [fix] Fixed some naming inconsistencies with postal/zip codes #343

## v2.1.32 (2021-01-21)

* [fix] Fix situations where the tax totals would be wrong with certain tax rates #340

## v2.1.31 (2021-01-21)

* [fix] ~~Fix situations where the tax totals would be wrong with certain tax rates #340~~

## v2.1.30 (2021-01-17)

* [new] Improved action responses (including propper support for AJAX usage)

## v2.1.29 (2021-01-14)

* [fix] Fixed issue with customer entry being overwritten by new carts. #337
* [fix] Fixed situation where exception would be thrown if the 'Maximum Uses' field isn't set #338

## v2.1.28 (2021-01-11)

* [new] Currency formatting now depends on current site locale, instead of always being `en_US`.
* [fix] Fixed issue with tax calculations #331
* [fix] Fixed Mollie Gateway issues and off-site gateway issues #334

## v2.1.27 (2021-01-11)

* [fix] Fixed `->get()` parameter issue when using Mollie webhook. #332

## v2.1.26 (2021-01-09)

* [fix] Sometimes tags were being called twice. Now it should just be once!
* [fix] Fixed exception sometimes if attempting variant augmentation on a product with no variants.
* [fix] Fixed issue where Gateway Webhook URL's were returned as `null`.

## v2.1.25 (2021-01-05)

* [fix] Fixed the way we handle fallback URLs for off-site gateways #329

## v2.1.24 (2021-01-04)

* [fix] Fixed exception thrown by Off-site gateway callback. #327
* [fix] If a redirect is not provided for off-site gateway, user should be redirected to the homepage.

## v2.1.23 (2020-12-28)

* [new] PHP 8 Support! #318
* [fix] Product entries with variants should not have a standard `price` field.
* [fix] The `has` method on Repositories will now return `null`, instead of throwing an exception about undefined indexes.

## v2.1.22 (2020-12-23)

* [fix] Fix issues parsing `null` Money fields. Addresses issue from #323.

## v2.1.21 (2020-12-23)

* [fix] Just get site with `Site::current()` inside Money Fieldtype, instead of getting the entries' locale.

## v2.1.20 (2020-12-21)

* [fix] Fixed issue when passing `receipt_email` to Stripe

## v2.1.19 (2020-12-21)

* [fix] Simplified the site detecting logic in the Money Fieldtype #319

## v2.1.18 (2020-12-18)

* [fix] Fixed issue with locales in Money Fieldtype again.

## v2.1.17 (2020-12-18)

* Re-tag of v2.1.16 (the release workflow broke)

## v2.1.16 (2020-12-18)

* [fix] Fix issue where `locale()` is called on undefined, in Money Fieldtype.

## v2.1.15 (2020-12-12)

* Remove Woodland

## v2.1.14 (2020-12-12)

* [new] You can now enable automatic receipt emails from Stripe.
* [new] You can now use a single address for an order, instead of different shipping and billing ones.
* [new] You can now set display names for gateways. Useful for when you give the customer an option between them.
* [fix] Fixed a bug causing type exceptions.
* [fix] Ensure customer can't add more of a product than you actually have in stock.

## v2.1.13 (2020-12-05)

* [new] Added some better error handling for form tags.
* [fix] Issue where it couldn't find an 'index' gateway using the `{{ sc:gateways }}` tag? Now sorted!

## v2.1.12 (2020-11-29)

A whole lot of API related changes this release...

* [new] It's now easier to get fields from your cart. How's `{{ sc:cart:delivery_note }}`?
* [new] The Order Calculator has been moved out of the `CartRepository` and into it's own class. However, the `recalculateTotals` method on the `CartRepository` will continue to work for the time being.
* [new] Introduced a new `OrderRepository` which will eventually replace the `CartRepository` (it's a breaking change so it won't happen until at least v2.2)
* [new] Added `customer` method to `CartRepository`
* [fix] Default order blueprint no longer has a SKU field on it.
* [fix] `php please sc:install` command will now only publish blueprints and configuration file.

## v2.1.11 (2020-11-27)

* [new] Add an `exempt_from_tax` field to products that you want to be exempt from tax.
* [fix] Carts & Customers will be created in the correct site.
* [fix] When created, customers will now be published instead of a draft.
* [fix] Money Fieldtype will respect the site of the entry and display the correct currency.
* [fix] Fixed issue where you could add the same product/variant to the cart twice.

## v2.1.10 (2020-11-22)

* [fix] Fixed bug with blueprint field checking

## v2.1.9 (2020-11-22)

* [new] Ensure fields are included in product & order blueprints.

## v2.1.8 (2020-11-21)

* [fix] Fix `vendor:publish` bug when running `php please sc:install` (I should really double check this stuff before I release it)

## v2.1.7 (2020-11-21)

* Re-tag of v2.1.6

## v2.1.6 (2020-11-21)

* [update] Improved the install process - combine `vendor:publish` step and `setup-content`.

## v2.1.5 (2020-11-13)

* [fix] A more descriptive message will now be shown if currency formatting fails due to the `php-intl` extension not being installed or enabled.'
* [fix] Fixed issue where gateways weren't being booted at all...

## v2.1.4 (2020-11-12)

* [fix] Fixed issue with [Woodland](https://github.com/doublethreedigital/simple-commerce/blob/master/src/Woodland.php) when installing Simple Commerce for the first time. #313
* [fix] Fixed issue with product variants fieldtype on new product entries. #314
* [fix] Fixed issue when adding a new variation in product variants fieldtype
* [fix] Localize and use plural/signular stuff with index text for product variants fields.

## v2.1.3 (2020-10-30)

* [fix] Fix issues with installing via Composer, from last release. Whoops.

## v2.1.2 (2020-10-30)

* [new] Licensing statistics - so we can check how many sites are using Simple Commerce, what versions are in use and if they have valid licenses.

## v2.1.1 (2020-10-28)

* [new] Simple Commerce fieldtypes will now display nicely in entry listings
* [fix] Fixed issue when using an off-site gateway without specifing a `redirect`
* Added a bunch of tests behind the scenes

## v2.1.0 (2020-10-18)

**v2.1 contains various breaking changes, we recommend reviewing [the update guide](https://sc-docs.doublethree.digital/v2.1/update-guide) to ensure your site will work with the update.**

* [new] [Product Variants](https://sc-docs.doublethree.digital/v2.1/product-variants)
* [new] [Built-in Mollie Gateway](https://sc-docs.doublethree.digital/v2.1/gateways#builtin-gateways)
* [new] Product Stock
* [new] Sales Widget - dashboard widget for reviewing sales over a week, 14 days and a month.
* [new] Support for Off-site gateways and a bunch of changes to the way gateways work overall.
* [new] Minimum Order Numbers
* [fix] Various bug fixes.

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
