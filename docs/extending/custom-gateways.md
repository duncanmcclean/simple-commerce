---
title: Building a custom gateway
---

There's a couple of cases where you might end up building a custom gateway. Either you need to extend an existing gateway and maybe send more/different information to the processor or you may need to connect with a payment processor that we don't include support for.

## Gateway types

Simple Commerce supports two types of gateways: on-site and off-site.

**On-site gateways** are ones on which the customer will enter their credit card information on your site. As an example: think [Stripe Elements](https://stripe.com/en-gb/payments/elements).

**Off-site gateways** are ones where the customer is redirected to the payment processor in order to enter their payment information and then once entered, they will usually be redirected back to your website. [Mollie](https://www.mollie.com/) is a good example of this.

## Creating your gateway

To get started: use the `make:gateway` command to generate the boilerplate code for your Payment Gateway. The first parameter will be the class name of your gateway, while the second should determine the [type of gateway](#content-gateway-types) you'd like to generate: `onsite` or `offsite`.

```
php please make:gateway PayMate onsite
```

Once created, you'll find the newly generated gateway in your `app/Gateways` folder.

## Explaining the methods

The boilerplate gateway has quite a few methods. Here's a quick overview of what each gateway method does and what you should return.

- `name()` - should return the name of your gateway
- `prepare()` - should be used to either: generate tokens used later on for displaying the payment form or generating an off-site checkout link.
- `purchase()` - should be used to do the actual purchase (aka. taking the money from the customer)
- `purchaseRules` - should return an array of [Laravel Validation Rules](https://laravel.com/docs/master/validation#available-validation-rules) for the checkout request.
- `purchaseMessages` (optional) - should return an array of validation messages for the checkout request, just like in a [Laravel Validation Request](https://laravel.com/docs/master/validation#using-rule-objects).
- `getCharge()` - should get information about a specific order's charge/transaction.
- `refundCharge()` - should refund an order
- `webhook()` - should accept incoming webhook payloads, used for off-site payment gateways.
- `paymentDisplay()` - should return an array with `text` and a `url` which will be returned by the 'Gateway Fieldtype'

## DTOs

DTOs (also known as Data Transfer Objects) are used to return information back from your gateway to Simple Commerce so it can process it. There's a couple gateway related ones you'll need to know about:

- [`Prepare`](https://github.com/doublethreedigital/simple-commerce/blob/main/src/Gateways/Prepare.php)
- [`Purchase`](https://github.com/doublethreedigital/simple-commerce/blob/main/src/Gateways/Purchase.php)
- [`Response`](https://github.com/doublethreedigital/simple-commerce/blob/main/src/Gateways/Response.php)

Each of these DTOs will have slightly different uses. You can view some examples of usage on some of the [built-in gateways](https://github.com/doublethreedigital/simple-commerce/tree/main/src/Gateways/Builtin).

## Off-site gateways

### Callback

Recently, a new `callback` method has been introduced for off-site gateways. It will be called by Simple Commerce whenever a user is redirected back to your site from the payment gateway.

The method is the deciding factor towards whether the payment was successful or not, this triggers error handling if the payment was not successful.

An example of where this is being used is in the `PayPalGateway`, see below:

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

This method may be useful if the status of a payment has not been made clear at the time the user comes back to the server (eg. webhook has not been received yet). You can instead do an API request, or whatever is required, to figure it the status.
