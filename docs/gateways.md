# Payment Gateways

Simple Commerce can be used with pretty much any payment gateway under the sun. Out of the box, you'll get a Stripe gateway, a manual gateway and a dummy gateway.

However, if you need one that's not included, create your own gateway.

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

## Creating your own gateway

It's easy to create your own gateway. Create a class that implements our `Gateway` interface. It'll scaffold out a few methods for you, just fill those in with stuff required for your gateway of choice.

```php
<?php

namespace YourName\YourGateway;

use Statamic\View\View;
use DoubleThreeDigital\SimpleCommerce\Gateways\Gateway;

class YourGateway implements Gateway
{
    public function completePurchase($data)
    {
        // stuff

        return [
            'is_paid' => true,
        ];
    }

    public function rules(): array
    {
        return [
            'cardholder' => 'required',
            'cardNumber' => 'required',
            'expiryMonth' => 'required',
            'expiryYear' => 'required',
            'cvc' => 'required',
        ];
    }

    public function paymentForm()
    {
        return (new View)
            ->template('your-gateay::payment-form')
            ->with([
                'class' => get_class($this),
            ]);
    }

    public function refund(array $gatewayData)
    {
        // stuff
    }

    public function name(): string
    {
        return 'Your Gateway';
    }
}
```

The `rules` method should return a Laravel validation array with any required fields for your gateway.

The `paymentForm` method should return an Antlers or Blade view with any HTML inputs or JavaScript required for it to work.

The `name` method should return a name for your payment gateway.
