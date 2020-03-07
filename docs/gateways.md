Simple Commerce can be used with pretty much any payment gateway under the sun. Out of the box, you'll get a Stripe gateway, a manual gateway and a dummy gateway.

However, if you need one that's not included, create your own gateway.

## Configuring a gateway

Gateways are easy enough to configure. Just add the namespaces of the gateways you want to use to your `config/simple-commerce.php` file.

```php
<?php

return [
    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\StripeGateway::class => [],
    ],
];
```

You can add as many gateways as need.

In your checkout form, you'll need to use the `commerce:gateways` tag to loop through each of the payment gateways and display their payment form.

```html
{{ commerce:gateways }}
    {{ payment_form }}
{{ /commerce:gateways }}
```

Maybe you could create a tabs thing or a toggle system in your UI. 

Some payment gateways also push their own scripts, for example Stripe requires Stripe Elements to be included and configured for the checkout form. You'll need to yield in scripts section into the bottom of your `<body>` element.

```html
{{ yield:scripts }}
```

## Creating your own gateway

It's easy to create your own gateway. Create a class that implements our `Gateway` interface. It'll scaffold out a few methods for you, just fill those in with stuff required for your gateway of choice.

```php
// TODO: update example here
```

TODO: update these method definitions

The `rules` method should return a Laravel validation array with any required fields for your gateway.

The `paymentForm` method should return an Antlers or Blade view with any HTML inputs or JavaScript required for it to work.

The `name` method should return a name for your payment gateway.
