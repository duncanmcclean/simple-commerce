# Extending Gateways

Sometimes you might need to build a store that needs its own custom payment gateway or needs to extend on an existing one.

::: v-pre

## Creating your own gateway

It's easy to create your own gateway. Create a class that implements our `Gateway` interface. It'll scaffold out a few methods for you, just fill those in with stuff required for your gateway of choice.

```php
<?php

namespace YourName\YourGateway;

use DoubleThreeDigital\SimpleCommerce\Models\Transaction;
use Illuminate\Support\Collection;
use Statamic\View\View;
use DoubleThreeDigital\SimpleCommerce\Gateways\Gateway;

class YourGateway implements Gateway
{
    public function completePurchase(array $data, float $total): Collection
    {
      	// Do some processing...
      
        return collect([
          'is_complete' => true,
          'amount' => $total,
          'data' => [
            'id' => 'AnIDFromYourGateway'
          ],
        ]);
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

    public function paymentForm(): string
    {
        return (new View)
            ->template('your-gateway::payment-form')
            ->with([
                'class' => get_class($this),
            ])
          	->render();
    }

    public function refund(Transaction $transaction): Collection
    {
        return collect([
            'is_refunded' => true,
        ]);
    }

    public function name(): string
    {
        return 'Your Gateway';
    }
}
```

The `rules` method should return a Laravel validation array with any required fields for your gateway.

The `paymentForm` method should return a string of HTML. You may use Antlers or Blade but make sure you render it before returning.

The `name` method should return a name for your payment gateway.

:::