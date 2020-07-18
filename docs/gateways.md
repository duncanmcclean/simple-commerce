# Payment Gateways

Simple Commerce works with any on-site payment gateways. Simple Commerce only provides a dummy gateway out of the box, but we've also created [a Stripe gateway](https://github.com/doublethreedigital/simple-commerce-stripe) that can be pulled in if needed. If you need a gateway, that we don't provide, you can create your own.

## Configuring a gateway

Gateways are easy enough to configure. Just add the namespaces of the gateways you want to use to your `config/simple-commerce.php` file.

```php
<?php

return [
    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
    ],
];
```

You can add as many gateways as want.

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

## First party gateways
Apart from the Dummy Gateway, Simple Commerce does not provide any payment gateways. However, you can install various other gateways.

* [Stripe for Simple Commerce](https://github.com/doublethreedigital/simple-commerce-stripe)

## Building your own gateway

You can learn more about writing your own gateway [over here](../extending/gateways).