<div class="card p-0 content">
    <div class="p-3">
        <h1>Recent Orders</h1>

        <div class="flex flex-col">
            @foreach($orders as $order)
                <div class="flex flex-row items-center justify-between py-2">
                    <a href="{{ cp_route('orders.edit', ['order' => $order['id']]) }}">{{ $order['slug'] }}</a>
                    <span>{{ \Carbon\Carbon::parse($order['order_date'])->toFormattedDateString() }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
