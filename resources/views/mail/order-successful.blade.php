@component('mail::message')
# Thanks!

Hello {{ $customer->name }},

Your order has been completed successfully. You can find all the information about your order below. Let us know if you have any questions.

@component('mail::table')
| Product       | Quantity      | Price    |
| ------------- |:-------------:| --------:|
@foreach($order->lineItems as $lineItem)
| {{ $lineItem->variant->product->title }} ({{ $lineItem->variant->sku }})       | {{ $lineItem->quantity }}      | {{ \DoubleThreeDigital\SimpleCommerce\Facades\Currency::parse($lineItem->total) }}    |
@endforeach
| Shipping       |        | {{ \DoubleThreeDigital\SimpleCommerce\Facades\Currency::parse($order->shipping_total) }}    |
| Tax            |        | {{ \DoubleThreeDigital\SimpleCommerce\Facades\Currency::parse($order->tax_total) }}    |
| **Total**          |        | {{ \DoubleThreeDigital\SimpleCommerce\Facades\Currency::parse($order->total) }}    |
@endcomponent

@component('mail::panel')
## Shipping Address

{{ $order->shippingAddress->name }},

{{ $order->shippingAddress->address1 }},

@if($order->shippingAddress->address2)
{{ $order->shippingAddress->address2 }},
@endif

@if($order->shippingAddress->address3)
{{ $order->shippingAddress->address3 }},
@endif

{{ $order->shippingAddress->city }},

@if($order->shippingAddress->state)
{{ $order->shippingAddress->state->name }},
@endif

{{ $order->shippingAddress->zip_code }},

{{ $order->shippingAddress->country->name }}
@endcomponent

@component('mail::panel')
## Billing Address

{{ $order->billingAddress->name }},

{{ $order->billingAddress->address1 }},

@if($order->billingAddress->address2)
{{ $order->billingAddress->address2 }},
@endif

@if($order->billingAddress->address3)
{{ $order->billingAddress->address3 }},
@endif

{{ $order->billingAddress->city }},

@if($order->billingAddress->state)
{{ $order->billingAddress->state->name }},
@endif

{{ $order->billingAddress->zip_code }},

{{ $order->billingAddress->country->name }}
@endcomponent
@endcomponent
