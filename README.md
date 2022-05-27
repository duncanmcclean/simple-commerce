<!-- statamic:hide -->

![Banner](./banner.png)

## Simple Commerce

<!-- /statamic:hide -->

Simple Commerce is a simple, yet powerful e-commerce addon for Statamic. You have complete control over the content structure and front-end of your site.

> Simple Commerce is the perfect e-commerce companion to Statamic. It gives you all the backend goodness you need for your e-commerce site, such as product management, orders and coupons, while letting you keep control over the customer-facing experience. Duncan has baked-in lots of flexibility, so if you need to roll your own payment provider, shipping integration or even tax calculations, you can do it. If you are looking to sell on Statamic, this should be your go to, our developers love working with it and our clients love using it!
>
> **Ryan Mitchell, [Thought Collective](https://www.thoughtcollective.com/)**

### Everything's just an entry

Stay with what you love - Statamic entries. With Simple Commerce, all of your products, orders, customers & coupons are Statamic entries. Giving you the flexibility you need to build bespoke e-commerce sites for your clients.

And, when your site grows, it's easy to [move your orders & customers](/database-orders) into a traditional database.

### Payment Gateways

Out-of-the-box, Simple Commerce ships with support for three of the big payment gateways: Stripe, PayPal and Mollie. Use whichever one you need, or if you need something else: it's easy to build one.

- [Documentation: Payment Gateways](https://simple-commerce.duncanmcclean.com/gateways)
- [Documentation: Building custom gateways](https://simple-commerce.duncanmcclean.com/extending/custom-gateways)

### Flexible Shipping

Provide your customers with different shipping options depending on their address.

- [Documentation: Shipping](https://simple-commerce.duncanmcclean.com/shipping)

### Discounting

Simple Commerce has support for discount coupons. You can limit coupons to certain products or to be used a max number of times. All your customer has to do is redeem the discount during checkout.

- [Documentation: Coupons](https://simple-commerce.duncanmcclean.com/coupons)

### Product Variants

Sometimes you need to sell different versions of the same product. Like a t-shirt, you might want to sell it in Small, Medium & Large - each with different prices and stock levels. That's where Product Variants come in. Create a single product and configure the variants inside it.

- [Documentation: Product Variants](https://simple-commerce.duncanmcclean.com/product-variants)

### Multi-site friendly

If you need to sell across different countries, you can take advantage of Statamic's multi-site feature in Simple Commerce to have a site per currency. Each site may also have its own set of shipping methods.

- [Documentation: Multi-site](https://simple-commerce.duncanmcclean.com/multisite)

### And lots more...

Simple Commerce might be simple but it's not basic - there's dozens of handy features to help you build small-medium e-commerce stores.

## Installation

> If you're starting a fresh site, you might be better using the Simple Commerce Starter Kit. [**→ Learn More**](https://github.com/doublethreedigital/sc-starter-kit#quick-start)

First, require Simple Commerce as a Composer dependency:

```
composer require doublethreedigital/simple-commerce
```

Next, run the `sc:install` command to publish Simple Commerce's config file, setup its collections and copy over any other necessary files.

```
php please sc:install
```

And you're done!

## Documentation

There's full documentation of Simple Commerce over on it's [documentation site](https://simple-commerce.duncanmcclean.com).

## Commercial addon

Simple Commerce is a commercial addon - you **must purchase a license** via the [Statamic Marketplace](https://statamic.com/addons/double-three-digital/simple-commerce) to use it in a production environment.

## Security

Only the latest version of Simple Commerce (v3.x) will receive security updates if a vulnerability is found.

If you discover a security vulnerability, please report it to Duncan straight away, [via email](mailto:security@doublethree.digital). Please don't report security issues through GitHub Issues.

## Official Support

If you're in need of some help with Simple Commerce, [send me an email](mailto:help@doublethree.digital) and I'll do my best to help! (I'll usually respond within a day)

## Other Repositories

- [**Starter Kit**](https://github.com/doublethreedigital/sc-starter-kit): Demo Templates & Boilerplate for your custom store
- [**Digital Products Addon**](https://github.com/doublethreedigital/sc-digital-products): Sell digital products with Simple Commerce

<!-- statamic:hide -->

---

<p>
<a href="https://statamic.com"><img src="https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge" alt="Compatible with Statamic v3"></a>
<a href="https://packagist.org/packages/doublethreedigital/simple-commerce/stats"><img src="https://img.shields.io/packagist/v/doublethreedigital/simple-commerce?style=for-the-badge" alt="Simple Commerce on Packagist"></a>
</p>

<!-- /statamic:hide -->
