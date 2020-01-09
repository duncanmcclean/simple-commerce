<div class="card p-0 content">
    <div class="p-3">
        <h1>Recent Customers</h1>

        <div class="flex flex-col">
            @foreach($customers as $customer)
                <div class="flex flex-row items-center justify-between py-2">
                    <a href="{{ cp_route('customers.edit', ['customer' => $customer['uid']]) }}">{{ $customer['name'] }}</a>
                    <span>{{ \Carbon\Carbon::parse($customer['customer_since'])->toFormattedDateString() }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
