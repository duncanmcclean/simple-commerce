# Payment Gateways

Simple Commerce ~~has~~ will have built-in support for popular payment gateways, like Stripe. However, if we don't support your gateway of choice, it's easy enough to build one yourself.

Gateways are configured in your `config/simple-commerce.php` file. Like so:

```
'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
],
```

The array key is the gateway class and the key should contain an array of settings for the gateway, such as API keys.

## Included Gateways

* Stripe (recommended)
* More coming soon!

### Dummy Gateway

We provide a dummy gateway which can be helpful for testing/prototyping, before you've decided on a particular gateway. When using the dummy gateway, all payments will be returned as successful, unless you use the card number `1212 1212 1212 1212`, where the payment will fail.

We include an example of the payment form for the Dummy gateway in the [Simple Commerce starter kit](https://github.com/doublethreedigital/simple-commerce-starter).

### Stripe Gateway

Stripe is the gateway we recommend, mostly because its modern and easy to use for both merchants and customers. To use the Stripe gateway in your store, add the Stripe class, followed by your Stripe API details as settings.

```
'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\StripeGateway::class => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
        ],
],
```

We would highly recommend making use of [environment variables](https://statamic.dev/configuration#environment-variables) to store your Stripe API details so they don't get leaked in version control (even if it's private).

We include an example of the payment form for the Stripe gateway in the [Simple Commerce starter kit](https://github.com/doublethreedigital/simple-commerce-starter). 

## Build a gateway

If you need to use a gateway that's not provided, you can build it yourself. The first thing you'll need to do is create a class, for example in `App\Gateways` called `SuperCoolGateway`.

You'll want that class to implement Simple Commerce's `Gateway` interface which defines all the required method for a gateway. A boilerplate gateway class looks something like this:

```
<?php

namespace App\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;

class SuperCoolGateway implements Gateway
{
    public function name(): string
    {
        return 'Super Cool';
    }

    public function prepare(array $data): array
    {
        return [];
    }

    public function purchase(array $data): array
    {
        return [];
    }

    public function purchaseRules(): array
    {
        return [];
    }

    public function getCharge(array $data): array
    {
        return [];
    }

    public function refundCharge(array $data): array
    {
        return [];
    }
}
```

Here's what each of those methods do:

* **name** - This method should return the name of your gateway. It's recommended this name should be recognisable.
* **prepare** - This method is called while loading the `sc:checkout` form. If you need to, you can return an array of stuff which will be available as variables inside the form.
* **purchase** - This method is called when the customer submits the `sc:checkout` form. `$data` will be everything sent to the checkout form. This is where you'll want to confirm the purchase.
* **purchaseRules** - This method should return an array of [validation rules](https://laravel.com/docs/7.x/validation#available-validation-rules) which will be used to make sure the request contains everything it needs to hit the `purchase` method later on.
* **getCharge** - This method will be hit anytime Simple Commerce needs to get gateway information.
* **refundCharge** - This method will be hit whenever a refund should happen. `$data` will be an array of the order's entry data.