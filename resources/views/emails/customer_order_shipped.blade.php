{{ $taxIncludedInPrices = config('simple-commerce.tax_engine_config.included_in_prices') }}

@component('mail::message')
# {{ __('Order Shipped') }}

{{ __('This email is to confirm that your order (**#:orderNumber**) has been marked as shipped. An overview of your order is shown below:', [
    'orderNumber' => $order->orderNumber(),
]) }}

@component('mail::table')
| {{ __('Items') }}       | {{ __('Quantity') }}         | {{ __('Total') }} |
| :--------- | :------------- | :----- |
@foreach ($order->lineItems() as $lineItem)
| [{{ $lineItem->product()->get('title') }}]({{ optional($lineItem->product()->resource())->absoluteUrl() }}) | {{ $lineItem->quantity() }} | {{ \DuncanMcClean\SimpleCommerce\Currency::parse($taxIncludedInPrices ? $lineItem->totalIncludingTax() : $lineItem->total(), $site) }} |
@endforeach
| | {{ __('Subtotal') }}: | {{ \DuncanMcClean\SimpleCommerce\Currency::parse($taxIncludedInPrices ? $order->itemsTotalWithTax() : $order->itemsTotal(), $site) }}
@if($order->coupon())
| | {{ __('Coupon') }}: | -{{ \DuncanMcClean\SimpleCommerce\Currency::parse($order->couponTotal(), $site) }}
@endif
| | {{ __('Shipping') }}: | {{ \DuncanMcClean\SimpleCommerce\Currency::parse($taxIncludedInPrices ? $order->shippingTotalWithTax() : $order->shippingTotal(), $site) }}
@if(!$taxIncludedInPrices)
| | {{ __('Tax') }}: | {{ \DuncanMcClean\SimpleCommerce\Currency::parse($order->taxTotal(), $site) }}
@endif
| | **{{ __('Total') }}:** | {{ \DuncanMcClean\SimpleCommerce\Currency::parse($order->grandTotal(), $site) }}
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

<br>

{{ __('If you have any questions about your order, please get in touch.') }}

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
