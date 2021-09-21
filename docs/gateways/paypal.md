---
title: PayPal Payment Gateway
---

> ✨ PayPal's still a fairly new gateway in Simple Commerce so if you spot any issues, please [open an issue](https://github.com/doublethreedigital/simple-commerce/issues/new/choose).

## Configuration

First, you'll need to add PayPal to your `simple-commerce.php` config file. You will also need to pass in `client_id`, `client_secret` and `environment` variables.

To obtain API credentials (provided you're already logged into PayPal), go to the [PayPal Developers site](https://developer.paypal.com/developer/applications), navigate to 'My Apps & Credentials'.

You can then create a sandbox/live application. At the end of the app creation progress, you'll be given the `client_id` and `client_secret` values.

```php
'gateways' => [
	\DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\PayPalGateway::class => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'environment' => env('PAYPAL_ENVIRONMENT', 'production'),
    ],
],
```

Make sure that `PAYPAL_ENVIRONMENT` is set to `sandbox` while you're in development. You don't want a mess on your hands because you accidentally autofilled your **REAL** card details. That would be bad. 😅

> It's best practice to use `.env` file for any API keys you need, rather than referencing them directly in your config file. [Review Statamic Docs](https://statamic.dev/configuration#environment-variables).

## Off-site payment flow

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

## On-site payment flow

Set the gateway configuretion to use on-site mode:

```php
'gateways' => [
	\DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\PayPalGateway::class => [
        ///
        'mode' => 'onsite',
    ],
],
```

The payment form should be included inside your `{{ sc:checkout }}` form, and any PayPal magic should also be wrapped in the `{{ sc:gateways }}` tag to ensure you can make full use of your gateway's configuration values.

A rough example of a PayPal implementation is provided below.

```antlers
<div id="paypal-button"></div>
<input id="paypal-payment-id" type="hidden" name="payment_id">
<script src="https://www.paypal.com/sdk/js?client-id={{ gateway-config:client_id }}&currency={{ paypal.result.currency_code }}"></script>
<script>
    paypal.Buttons({
        createOrder: () => {
            return Promise.resolve('{{ paypal.result.id }}');
        },
        onApprove: (data, actions) => {
            document.getElementById('paypal-payment-id').value = data.orderID;
            document.getElementById('checkout-form').submit();
        },
    }).render('#paypal-button');
</script>
```

### Handling PayPal's webhook

The PayPal gateway has a webhook which is hit by PayPal whenever a payment is made.

Unfortunatley, PayPal offers no way for Simple Commerce to configure the webhook on your behalf, so you'll need to add it yourself.

1. In the PayPal Developers site, navigate to 'My Apps & Credentials'
2. Click into the application being used for this site/environment.
3. At the bottom of the page, you'll see the 'Webhooks' section, create a new webhook.
4. The Webhook URL should be: `https://example.com/!/simple-commerce/gateways/paypal/webhook`
5. Under 'Event types', you should select 'All events'. You can then save the webhook.

You will also need to add the webhook's URL to your list of CSRF exceptions, which can be found in `app/Http/Middleware/VerifyCsrfToken.php`.

```php
protected $except = [
  '/!/simple-commerce/gateways/paypal/webhook',
];
```

When you're going through the payment flow in your development environment, you will need to use something like Expose or Ngrok to proxy request to your local server. Otherwise, PayPal wouldn't be able to hit the webhook. You will also need to update the `APP_URL` in your `.env`.

### Handling the redirect back to your site

On the return back to your site from PayPal, you can have customers redirected to seperate URLs, depending on whether the payment was successful or failed/cancelled.

The `redirect` parameter on the `sc:checkout:paypal` tag will handle the successful payment redirects.

Where as `error_redirect` will handle any other payment states.
