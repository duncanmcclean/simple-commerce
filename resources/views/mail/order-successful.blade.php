@component('mail::message')
# Your Order #{{ $order->id }}

Hi {{ $customer->name }},

Thanks for ordering from {{ config('app.name') }}. This email is the receipt for your purchase.

@component('mail::table')
| Product       | Quantity         | Price                  |
| ------------- |:----------------:| ----------------------:|
@foreach($order->items['items'] as $item)
| {{ \DoubleThreeDigital\SimpleCommerce\Models\Product::find($item->product_id)->title }} ({{ \DoubleThreeDigital\SimpleCommerce\Models\Variant::find($item->variant_id)->sku }}) | {{ $item->quantity }} | $15.00 |
@endforeach

@foreach($order->items['shipping'] as $item)
| **Shipping:** {{ \DoubleThreeDigital\SimpleCommerce\Models\ShippingZone::find($item->shipping_zone_id)->country->name }} TODO: state name, {{ \DoubleThreeDigital\SimpleCommerce\Models\ShippingZone::find($item->shipping_zone_id)->start_of_zip_code }} | N/A | {{ \DoubleThreeDigital\SimpleCommerce\Models\ShippingZone::find($item->shipping_zone_id)->rate }} |
@endforeach

@foreach($order->items['tax'] as $item)
| **Tax:** {{ \DoubleThreeDigital\SimpleCommerce\Models\ShippingZone::find($item->shipping_zone_id)->country->name }} TODO: state name, {{ \DoubleThreeDigital\SimpleCommerce\Models\ShippingZone::find($item->shipping_zone_id)->start_of_zip_code }} | N/A | {{ \DoubleThreeDigital\SimpleCommerce\Models\ShippingZone::find($item->shipping_zone_id)->rate }}% |
@endforeach

| - | Items Sub Total | {{ $order->items['totals']->items  }} |
| - | Total Discount | $0.00 |
| - | Total Shipping | {{ $order->items['totals']->shipping }} |
| - | Total Tax | {{ $order->items['totals']->tax }} |
| - | **Total Price** | **{{ $order->items['totals']->overall }}** |
@endcomponent

{{--    @component('mail::panel')--}}
{{--        ## Shipping Address--}}

{{--        {{ $order->shippingAddress()->name }},--}}
{{--        {{ $order->shippingAddress()->address1 }},--}}
{{--        @if ($order->shippingAddress()->address2 != null)--}}
{{--            {{ $order->shippingAddress()->address2 }}--}}
{{--        @endif--}}
{{--        @if ($order->shippingAddress()->address3 != null)--}}
{{--            {{ $order->shippingAddress()->address3 }}--}}
{{--        @endif--}}
{{--        {{ $order->shippingAddress()->city }},--}}
{{--        @if (isset($order->shippingAddress()->state->name))--}}
{{--            {{ $order->shippingAddress()->city()->name }}--}}
{{--        @endif--}}
{{--        {{ $order->shippingAddress()->zip_code }},--}}
{{--        {{ $order->shippingAddress()->country()->name }}--}}
{{--    @endcomponent--}}

{{--    @component('mail::panel')--}}
{{--        ## Billing Address--}}

{{--        {{ $order->billingAddress()->name }},--}}
{{--        {{ $order->billingAddress()->address1 }},--}}
{{--        @if ($order->billingAddress()->address2 != null)--}}
{{--            {{ $order->billingAddress()->address2 }}--}}
{{--        @endif--}}
{{--        @if ($order->billingAddress()->address3 != null)--}}
{{--            {{ $order->billingAddress()->address3 }}--}}
{{--        @endif--}}
{{--        {{ $order->billingAddress()->city }},--}}
{{--        @if (isset($order->billingAddress()->state->name))--}}
{{--            {{ $order->billingAddress()->city()->name }}--}}
{{--        @endif--}}
{{--        {{ $order->billingAddress()->zip_code }},--}}
{{--        {{ $order->billingAddress()->country()->name }}--}}
{{--    @endcomponent--}}

We'll let you know when your items have been dispatched.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
