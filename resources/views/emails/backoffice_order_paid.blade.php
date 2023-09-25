@component('mail::message')
# {{ __('New Order') }}

{{ __('This email is to confirm that a new order has been placed. An overview of the order is shown below:') }}

**{{ __('Order Number') }}:** #{{ $order->orderNumber() }}

@component('mail::table')
| {{ __('Items') }}       | {{ __('Quantity') }}         | {{ __('Total') }} |
| :--------- | :------------- | :----- |
@foreach ($order->lineItems() as $lineItem)
| [{{ $lineItem->product()->get('title') }}]({{ optional($lineItem->product()->resource())->absoluteUrl() }}) | {{ $lineItem->quantity() }} | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($lineItem->totalIncludingTax(), $site) }} |
@endforeach
| | {{ __('Subtotal') }}: | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->itemsTotalWithTax(), $site) }}
@if($order->coupon())
| | {{ __('Coupon') }}: | -{{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->couponTotal(), $site) }}
@endif
| | {{ __('Shipping') }}: | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->shippingTotalWithTax(), $site) }}
| | **{{ __('Total') }}:** | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->grandTotal(), $site) }}
| | |
@endcomponent

## {{ __('Customer Details') }}

@if($order->customer())
* **{{ __('Name') }}:** {{ $order->customer()->name() }}
* **{{ __('Email') }}:** {{ $order->customer()->email() }}
@endif

@if($order->billingAddress())
* **{{ __('Billing Address') }}:** {{ $order->billingAddress()->__toString() }}
@endif

@if($order->shippingAddress())
* **{{ __('Shipping Address') }}:** {{ $order->shippingAddress()->__toString() }}
@endif

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
