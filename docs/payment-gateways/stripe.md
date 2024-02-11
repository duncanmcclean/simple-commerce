---
title: Stripe Payment Gateway
---

## Configuration

First, you'll need to add Stripe to your `simple-commerce.php` config file. You will also need to pass in a `key` and `secret` file.

You can obtain [your API keys](https://dashboard.stripe.com/test/apikeys) from the Stripe Dashboard.

```php
'gateways' => [
	\DuncanMcClean\SimpleCommerce\Gateways\Builtin\StripeGateway::class => [
    	'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
],
```

> It's best practice to use `.env` file for any API keys you need, rather than referencing them directly in your config file. [Review Statamic Docs](https://statamic.dev/configuration#environment-variables).

## Payment flow

Since v5.x, Stripe is considered an off-site gateway, which means after the customer has entered their payment details, they're redirected (by Stripe) to a 'checkout success' page. Here's a quick run down of how the whole process works:

1. On your "Checkout" page, the customer enters their payment information
2. When the user submits the checkout page, their payment is confirmed by Stripe
3. Stripe sends a webhook to the server, letting Simple Commerce know the payment has taken place.
4. The customer is then redirected (by Stripe) to a Simple Commerce URL which then redirects the user to a 'checkout success' page.

## Checkout page

Stripe recommends using their [Stripe Elements](https://stripe.com/en-gb/payments/elements) library to capture payment information. It means that your customer's payment details never touch your server, only Stripe's.

On your checkout page, you'll want to loop through `{{ sc:gateways }}`. When it's Stripe, you'll want to display the Stripe Payment Element on your page, like so:

```antlers
<div>
    <div id="payment-element">
        <!--Stripe.js injects the Payment Element-->
    </div>
    <div id="payment-message" class="hidden"></div>
</div>

<input id="stripePaymentMethod" type="hidden" name="payment_method">

<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('{{ stripe:config:key }}');

    let elements;

    checkStatus();

    elements = stripe.elements({
        clientSecret: '{{ stripe:client_secret }}'
    });

    const paymentElementOptions = {
        layout: "tabs",
    };

    const paymentElement = elements.create("payment", paymentElementOptions);
    paymentElement.mount("#payment-element");

    async function confirmPayment() {
        const {
            error
        } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: "{{ stripe:callback_url }}",
            },
        });

        // This point will only be reached if there is an immediate error when
        // confirming the payment. Otherwise, your customer will be redirected to
        // your `return_url`. For some payment methods like iDEAL, your customer will
        // be redirected to an intermediate site first to authorize the payment, then
        // redirected to the `return_url`.
        if (error.type === "card_error" || error.type === "validation_error") {
            showMessage(error.message);
        } else {
            showMessage("An unexpected error occurred.");
        }
    }

    // Fetches the payment intent status after payment submission
    async function checkStatus() {
        const clientSecret = new URLSearchParams(window.location.search).get(
            "payment_intent_client_secret"
        );

        if (!clientSecret) {
            return;
        }

        const {
            paymentIntent
        } = await stripe.retrievePaymentIntent(clientSecret);

        switch (paymentIntent.status) {
            case "succeeded":
                showMessage("Payment succeeded!");
                break;
            case "processing":
                showMessage("Your payment is processing.");
                break;
            case "requires_payment_method":
                showMessage("Your payment was not successful, please try again.");
                break;
            default:
                showMessage("Something went wrong.");
                break;
        }
    }

    // ------- UI helpers -------

    function showMessage(messageText) {
        const messageContainer = document.querySelector("#payment-message");

        messageContainer.classList.remove("hidden");
        messageContainer.textContent = messageText;

        setTimeout(function() {
            messageContainer.classList.add("hidden");
            messageText.textContent = "";
        }, 4000);
    }
</script>
```

When using the above example, you'll want to hit the `confirmPayment` function whenever your customer submits the checkout form.

### Handling Stripe's webhook

Whenever a payment is made with Stripe, it needs to be able to communicate that with Simple Commerce. It does this using 'webhooks', which are essentially `POST` requests sent by Stripe to your server that provide details about the payment.

You'll need to configure the webhook in the Stripe Dashboard:

1. Navigate to the 'Developers' section (top right)
2. If you're in development, ensure you have the 'test mode' toggle enabled (also in the top right)
3. On the left hand side, click on 'Webhooks'
4. Click 'Add an endpoint'
5. You'll now be asked to enter details about your new webhook:
    - Endpoint URL: This will be the URL of the webhook, like `https://example.com/!/simple-commerce/gateways/stripe/webhook` (ensure that URL is reachable by Stripe - see note below)
    - Version: Select the latest version available
    - Select events to listen to:
        - `payment_intent.succeeded`
        - `payment_intent.failed`
        - `charge.refunded`

:::note Note!
When you're going through the payment flow in your development environment, you will need to use something like Expose or Ngrok to proxy request to your local server. Otherwise, Stripe wouldn't be able to hit the webhook. You will also need to update the `APP_URL` in your `.env`.
:::

## Testing

Stripe provides ways to test different scenarios, depending on your payment method. You can review [Stripe's documentation](https://stripe.com/docs/testing) for more details.

## Customisation

### Payment Intent

During the 'prepare' stage, Simple Commerce creates a [Stripe Payment Intent](https://stripe.com/docs/payments/payment-intents#creating-a-paymentintent). The Payment Intent generates a 'client secret' which is later given to Stripe Elements to render the payment fields.

Simple Commerce will automatically set the amount, currency, description and order ID (as metadata) on the Payment Intent. However, there can be times where you may need to add to the array that's sent.

You may do this easily by providing a closure in the Stripe gateway config:

```php
'gateways' => [
	\DuncanMcClean\SimpleCommerce\Gateways\Builtin\StripeGateway::class => [
    	'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'payment_intent_data' => function ($order) {
            return [
                'metadata' => [
                    'product_ids' => $order->lineItems()->pluck('product')->join(', '),
                ],
            ];
        },
    ],
],
```

The closure should accept an `$order` parameter and should then return an array which will be merged with the defaults.

:::warning Warning!
Laravel doesn't support using closures inside config files when using config caching.
:::

## Can I use Stripe Checkout?

Unfortunately, no. 😞

I've explored implementing Stripe Checkout a few times. However, every time I've decided against it since it takes over too much of the checkout process, to a point where you'd be better off not using Simple Commerce at all.

For example: Stripe Checkout wants you to use Stripe's Coupons/Discounts, wants customers to enter shipping/billing information on their page, stock needs to be managed in Stripe, tax & shipping would need to work differently.

Yes, you could argue that some of those things could be mapped across from Simple Commerce into Stripe. However, that's probably more work than it's actually worth.
