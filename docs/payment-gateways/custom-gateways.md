---
title: Building a custom gateway
---

There's a couple of cases where you might end up building a custom gateway. Either you need to extend an existing gateway and maybe send more/different information to the processor or you may need to connect with a payment processor that we don't include support for.

## Different types of gateways

Simple Commerce supports two types of gateways: on-site and off-site.

**On-site gateways** - these are gateways where the customer will enter their payment details directly on your site, using the `{{ sc:checkout }}` tag.

**Off-site gateways** - these are gateways where the customer is redirected to the payment gateway's website in order to enter their payment details. Once entered, they'll usually be redirected back onto your website. [Mollie](https://www.mollie.com/) is a good example of this.

## Creating your gateway

To get started: use the `make:gateway` command to generate the boilerplate code for your Payment Gateway. The first parameter will be the class name of your gateway, while the second should determine the [type of gateway](#content-gateway-types) you'd like to generate: `onsite` or `offsite`.

```
php please make:gateway PayMate onsite
```

Once created, you'll find the newly generated gateway in your `app/Gateways` folder.

## Handling on-site payments

On-site gateways will have a number of checkout-related methods:

### `checkout`

This method is called when you submit the `{{ sc:checkout }}` form. It should return an array of payment data that'll be saved onto the order.

If you need to display an error message, you should throw a GatewayCheckoutFailed exception.

### `checkoutRules`

This method should return an array of validation rules that'll be run whenever the `{{ sc:checkout }}` has been submitted.

### `checkoutMessages`

This method should return an array of validation messages that'll be used whenever the `{{ sc:checkout }}` has been submitted. This method isn't mandatory.

### Example

Taken directly from Simple Commerce's Stripe implementation, here's an example on how to implement these methods for an on-site gateway:

```php
public function checkout(Request $request, OrderContract $order): array
{
    $paymentIntent = PaymentIntent::retrieve($order->get('stripe')['intent']);
    $paymentMethod = PaymentMethod::retrieve($request->payment_method);

    if ($paymentIntent->status === 'succeeded') {
        $this->markOrderAsPaid($order);
    }

    return [
        'id'       => $paymentMethod->id,
        'object'   => $paymentMethod->object,
        'card'     => $paymentMethod->card->toArray(),
        'customer' => $paymentMethod->customer,
        'livemode' => $paymentMethod->livemode,
        'payment_intent' => $paymentIntent->id,
    ];
}

public function checkoutRules(): array
{
    return [
        'payment_method' => ['required', 'string'],
    ];
}

public function checkoutMessages(): array
{
    return [
        'payment_method.required' => 'The Stripe Payment Method is required when submitting the checkout form.',
    ];
}
```

## Handling off-site payments

### Off-site payment flow

1. After the customer has filled their information/shipping address, they're redirected to the payment gateway's website
2. The customer enters their payment information on the payment gateway's site.
3. The payment gateway sends a request back to Simple Commerce using a _webhook_, letting it know the payment is complete (or if it's not, that it's failed)!
4. The customer is then redirected back to the _callback URL_ and presented with a "Thanks for your order" page.

### Redirecting to payment gateway's website

To redirect a customer to the payment gateway, you can use the `{{ sc:checkout }}` tag, just include the name of the off-site gateway you wish to use. Like so:

```antlers
{{ sc:checkout:mollie redirect="/thanks" error_redirect="/payment-error" }}
```

In the above example, `mollie` is the off-site gateway.

However, bear in mind that where-ever you use that tag, the customer will be redirected away from your site. So it's probably best to have it sitting on it's own page.

### Webhooks

When anything changes payment-wise on the order, the off-site gateway will send a request to the configured webhook URL letting it know the status of the payment.

Webhook URLs look a little something like this: `/!/simple-commerce/gateways/YOUR_GATEWAY_NAME/webhook`

:::note Note!
When you're going through the payment flow in your development environment, you will need to use something like Expose or Ngrok to proxy request to your local server. Otherwise, Mollie wouldn't be able to hit the webhook. You will also need to update the `APP_URL` in your `.env`.
:::

Now, in your gateway, you can do whatever you need to do to check the status of the payment & update the order's status if necessary.

```php
public function webhook(Request $request)
{
    $mollieId = $request->get('id');

    $payment = $this->mollie->payments->get($mollieId);

    if ($payment->status === MolliePaymentStatus::STATUS_PAID) {
        $order = OrderFacade::query()
            ->where('data->mollie->id', $request->get('id'))
            ->first();

        if (! $order) {
            throw new OrderNotFound("Order related to Mollie transaction [{$mollieId}] could not be found.");
        }

        if ($order->paymentStatus() === PaymentStatus::Paid) {
            return;
        }

        $this->markOrderAsPaid($order);
    }
}
```

### Callback URL

The Callback URL is where your customers will hit when being redirected back from your payment gateway.

It's the code that'll decide if your customer is redirected to the `redirect` URL you specified earlier (as a parameter on the `{{ sc:checkout:NAME }}` tag) or the `error_redirect` tag.

An example is provided below of how we handle this in the `PayPalGateway`:

```php
public function callback(Request $request): bool
{
    $this->setupPayPal();

    $order = OrderFacade::find($request->get('_order_id'));

    if (! $order) {
        return false;
    }

    $paypalOrder = $order->get('paypal')['result'];

    $request = new OrdersGetRequest($paypalOrder['id']);

    $response = $this->paypalClient->execute($request);

    return $response->result->status === 'APPROVED';
}
```

Depending on your gateway, it's possible that your customer may reach your callback URL **before** the webhook request has been processed. You may wish to check-in with your gateway's API directly in the `callback` method.

## Fieldtype Display

`fieldtypeDisplay` might be a bit of a confusing method name. However, this method is responsible for what's displayed when the payment for an order is viewed within the Control Panel.

It's recommended that `text` returns the payment's ID (or something else that's unique to the payment) and the `url` be the URL to view the payment in the payment gateway's dashboard.

If your payment gateway doesn't have a dashboard, `url` can be `null`.

## Digging deeper

If you need more information about a specific method or what parameters should be passed in/out, please review [the `Gateway` contract](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Contracts/Gateway.php) which includes some helpful docblocks.
