@component('mail::message')
# Order Confirmation

This email is to confirm that your order (**#{{ $order->orderNumber() }}**) has been marked as paid. An overview of your order is shown below:

@component('mail::table')
| Items       | Quantity         | Total |
| :--------- | :------------- | :----- |
@foreach ($order->lineItems() as $lineItem)
@php
$site = \Statamic\Facades\Site::current();
@endphp
| [{{ $lineItem->product()->get('title') }}]({{ optional($lineItem->product()->resource())->absoluteUrl() }}) | {{ $lineItem->quantity() }} | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($lineItem->total(), $site) }} |
@endforeach
| | Subtotal: | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->itemsTotal(), $site) }}
@if($order->coupon())
| | Coupon: | -{{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->couponTotal(), $site) }}
@endif
| | Shipping: | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->shippingTotal(), $site) }}
| | Tax: | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->taxTotal(), $site) }}
| | **Total:** | {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->grandTotal(), $site) }}
| | |
@endcomponent

## Customer Details

@if($order->customer())
* **Name:** {{ $order->customer()->name() }}
* **Email:** {{ $order->customer()->email() }}
@endif

@if($order->billingAddress())
* **Billing Address:** {{ $order->billingAddress()->__toString() }}
@endif

@if($order->shippingAddress())
* **Shipping Address:** {{ $order->shippingAddress()->__toString() }}
@endif

<br>

If you have any questions about your order, please get in touch.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
