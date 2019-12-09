@component('mail::message')
    # Order successful

    Hi {{ $customer->name }},

    Thanks for ordering from {{ config('commerce.company.name') }}. This email is your receipt for your purchase.

    @component('mail::table')
        | Product       | Quantity         | Amount (inc Shipping)  |
        | ------------- |:----------------:| ----------------------:|
        @foreach($order->products as $item)
            | {{ $item->title }} | {{ $item->quantity }} | {{ $item->price }} |
        @endforeach
        | &nbsp; |	&nbsp; | {{ config('commerce.currency.symbol') }}{{ $order->total }} |
    @endcomponent

    @component('mail::panel')
        **Shipping Address**

        {{ $customer->address }}
        {{ $customer->country }}
        {{ $customer->zip_code }}
    @endcomponent

    If you have any questions about your order, simply reach out to {{ config('commerce.company.email') }}

    Thanks,<br>
    {{ config('commerce.company.name') }}
@endcomponent
