---
title: Stripe Payment Gateway
---

## Configuration

First, you'll need to add Stripe to your `simple-commerce.php` config file. You will also need to pass in a `key` and `secret` file.

You can obtain [your API keys](https://dashboard.stripe.com/test/apikeys) from the Stripe Dashboard.

```php
'gateways' => [
	\DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\StripeGateway::class => [
    	'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
],
```

> It's best practice to use `.env` file for any API keys you need, rather than referencing them directly in your config file. [Review Statamic Docs](https://statamic.dev/configuration#environment-variables).

## Payment Form

Stripe recommend using their Elements library to capture credit card information as it means your customers' card information never touches your server, which is good for a whole load of reasons.

The payment form should be included inside your `{{ sc:checkout }}` form, and any Stripe Elements magic should also be wrapped in the `{{ sc:gateways }}` tag to ensure you can make full use of your gateway's configuration values.

A rough example of a Stripe Elements implementation is provided below.

```antlers
<div>
    <label for="card-element">Card Details</label>
    <div id="card-element"></div>
</div>

<input id="stripePaymentMethod" type="hidden" name="payment_method">
<input type="hidden" name="gateway" value="DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\StripeGateway">

<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('{{ gateway-config:key }}')
    var elements = stripe.elements()

    const card = elements.create('card')
    card.mount('#card-element')

    card.addEventListener('change', ({error}) => {
        // Deal with errors
    })

    function confirmPayment() {
        stripe.confirmCardPayment('{{ client_secret }}', {
            payment_method: { card: card },
        }).then(function (result) {
          	if (result.paymentIntent.status === 'succeeded') {
            	document.getElementById('stripePaymentMethod').value = result.paymentIntent.payment_method
            } else if (result.error) {
             	// Deal with errors
            }
        })
    }
</script>
```

Bearing in mind, you will need to use that inside of a `{{ sc:gateways }}` tag in order to use any values from your gateway config.
