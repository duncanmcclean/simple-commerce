---
title: Mollie Payment Gateway
---

## Configuration

First, you'll need to add Mollie to your `simple-commerce.php` config file. You will also need to pass in `key` and `secret` variables.

You can obtain your API keys from the Mollie Dashboard.

![Mollie API Keys](/img/simple-commerce/mollie-dev-api-keys.png)

```php
'gateways' => [
	\DuncanMcClean\SimpleCommerce\Gateways\Builtin\MollieGateway::class => [
    	'key' => env('MOLLIE_KEY'),
        'profile' => env('MOLLIE_PROFILE'),
    ],
],
```

> It's best practice to use `.env` file for any API keys you need, rather than referencing them directly in your config file. [Review Statamic Docs](https://statamic.dev/configuration#environment-variables).

## Payment flow

Mollie is an off-site gateway, which means the customer is redirected onto Mollie's checkout page to complete payment. Here's a quick run down of how the whole process works:

1. After filling out shipping info etc, the store redirects the customer to Mollie
2. The customer enters their payment information on Mollie's checkout
3. If the payment is successful, a webhook is sent to your server.
4. The customer is then redirected back to your site.

### Handing the user off to Mollie's checkout

To redirect the customer off to Mollie's checkout page, you can use the `sc:checkout:mollie` tag.

```antlers
{{ sc:checkout:mollie redirect="/thanks" error_redirect="/payment-error" }}
```

However, bear in mind that where-ever you use that tag, the customer will be redirected away from your site. So it's probably best to have it sitting on it's own page.

### Handling Mollie's webhook

The Mollie gateway has a webhook which is hit by Mollie whenever a payment is made.

:::note Note!
When you're going through the payment flow in your development environment, you will need to use something like Expose or Ngrok to proxy request to your local server. Otherwise, Mollie wouldn't be able to hit the webhook. You will also need to update the `APP_URL` in your `.env`.
:::

### Handling the redirect back to your site

On the return back to your site from Mollie, you can have customers redirected to seperate URLs, depending on whether the payment was successful or failed/cancelled.

The `redirect` parameter on the `sc:checkout:mollie` tag will handle the successful payment redirects.

Where as `error_redirect` will handle any other payment states.
