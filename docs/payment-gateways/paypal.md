---
title: PayPal Payment Gateway
---

Simple Commerce supports accepting payments through PayPal in two forms: on-site and off-site. On-site is where the customer stays on your website for the checkout process and they see a PayPal embed where they can enter their payment information. Off-site is where you redirect the user to PayPal to complete the payment process there.

## Configuration

First, you'll need to add PayPal to your `simple-commerce.php` config file. You will also need to pass in `client_id`, `client_secret` and `environment` variables.

To obtain API credentials (provided you're already logged into PayPal), go to the [PayPal Developers site](https://developer.paypal.com/developer/applications), navigate to 'My Apps & Credentials'.

You can then create a sandbox/live application. At the end of the app creation progress, you'll be given the `client_id` and `client_secret` values.

```php
'gateways' => [
	\DuncanMcClean\SimpleCommerce\Gateways\Builtin\PayPalGateway::class => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'environment' => env('PAYPAL_ENVIRONMENT', 'production'),
        'mode' => 'offsite', // Either: offsite OR onsite
    ],
],
```

Make sure that `PAYPAL_ENVIRONMENT` is set to `sandbox` while you're in development. You don't want a mess on your hands because you accidentally autofilled your **REAL** card details. That would be bad. 😅

> It's best practice to use `.env` file for any API keys you need, rather than referencing them directly in your config file. [Review Statamic Docs](https://statamic.dev/configuration#environment-variables).

You may also specify the 'mode' to run the PayPal gateway in. You can either run it in `offsite` or `onsite` mode.

## Using as an off-site gateway

### Payment flow

Here's a quick run down of how the whole process works:

1. After filling out shipping info etc, the store redirects the customer to PayPal
2. The customer enters their payment information on PayPal's checkout
3. The customer is then redirected back to your site.
4. If the payment was successful, a webhook is sent to your server.

### Handing the user off to PayPal's checkout

To redirect the customer off to PayPal's checkout page, you can use the `sc:checkout:paypal` tag.

```antlers
{{ sc:checkout:paypal redirect="/thanks" error_redirect="/payment-error" }}
```

However, bear in mind that where-ever you use that tag, the customer will be redirected away from your site. So it's probably best to have it sitting on it's own page.

### Handling the redirect back to your site

On the return back to your site from PayPal, you can have customers redirected to seperate URLs, depending on whether the payment was successful or failed/cancelled.

The `redirect` parameter on the `sc:checkout:paypal` tag will handle the successful payment redirects.

Where as `error_redirect` will handle any other payment states.

## Using as an on-site gateway

### Payment flow

1. The customer goes to a Checkout page on your website. Somewhere on that page is a 'PayPal button'
2. The 'PayPal button' then asks you to enter your card details, or login with PayPal
3. Once the user has entered their details, the customer submits the checkout form
4. When the checkout form is submitted, the payment will be marked as paid
5. The user will then be redirect to a successful purchase screen

### Templating

The payment form should be included inside your `{{ sc:checkout }}` form, and any PayPal magic should also be wrapped in the `{{ sc:gateways }}` tag to ensure you can make full use of your gateway's configuration values.

A rough example of a PayPal implementation is provided below.

```antlers
<div id="paypal-button"></div>
<input id="paypal-payment-id" type="hidden" name="payment_id">
<input type="hidden" name="gateway" value="DuncanMcClean\SimpleCommerce\Gateways\Builtin\PayPalGateway">
<script src="https://www.paypal.com/sdk/js?client-id={{ paypal:config:client_id }}&currency={{ result.currency_code }}"></script>
<script>
    paypal.Buttons({
        createOrder: () => {
            return Promise.resolve('{{ result.id }}');
        },
        onApprove: (data, actions) => {
            document.getElementById('paypal-payment-id').value = data.orderID;
            document.getElementById('checkout-form').submit();
        },
    }).render('#paypal-button');
</script>
```

## Handling PayPal's webhook

Whenever a payment is made with PayPal, it needs to be able to communicate that with Simple Commerce. It does this using 'webhooks', which are essentially `POST` requests sent by PayPal to your server that provide details about the payment.

Unfortunatley, PayPal offers no way for Simple Commerce to configure the webhook on your behalf, so you'll need to add it yourself.

1. In the PayPal Developers site, navigate to 'My Apps & Credentials'
2. Click into the application being used for this site/environment.
3. At the bottom of the page, you'll see the 'Webhooks' section, create a new webhook.
4. The Webhook URL should be: `https://example.com/!/simple-commerce/gateways/paypal/webhook`
5. Under 'Event types', you should select 'All events'. You can then save the webhook.

:::note Note!
When you're going through the payment flow in your development environment, you will need to use something like Expose or Ngrok to proxy request to your local server. Otherwise, Mollie wouldn't be able to hit the webhook. You will also need to update the `APP_URL` in your `.env`.
:::

## Testing

PayPal themselves don't offer a set of 'test cards' like Stripe, or other common payment gateways do. Instead, you have to use fake card numbers and hope PayPal will accept them. Here's a couple we've found you can use for testing:

-   `4485165985805957`
-   `4485250194331820`
-   `375527918910414`
-   `375363946611620`
-   `5557514931753152`
-   `5277359932030179`
