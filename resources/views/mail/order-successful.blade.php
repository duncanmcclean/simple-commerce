@component('mail::message')
    # Order successful

    Hi {{ $customer['name'] }},

    Thanks for ordering from {{ config('app.name') }}. This email is receipt of your purchase.

    @component('mail::table')
        | Product       | Quantity         | Amount (inc Shipping)  |
        | ------------- |:----------------:| ----------------------:|
        @foreach($products as $product)
        | {{ $product['title'] }} | {{ $product['quantity'] }} | {{ config('commerce.currency.symbol') }}{{ $product['price'] }} |
        @endforeach
        | &nbsp; |	&nbsp; | {{ config('commerce.currency.symbol') }}{{ $order['total'] }} |
    @endcomponent

    @component('mail::panel')
        **Shipping Address**

        {{ $order['address'] }}
        {{ $order['country'] }}
        {{ $order['zip_code'] }}
    @endcomponent

    If you have any questions about your order, simply reach out to {{ config('commerce.company.email') }}.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
