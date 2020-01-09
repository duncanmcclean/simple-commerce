<div class="card p-0 rounded-lg pb-2">
    <div class="flex justify-between items-center p-2">
        <h2>Recent Orders</h2>
        <a href="{{ cp_route('orders.create') }}" class="text-blue hover:text-blue-dark text-sm">New Order</a>
    </div>

    @if($orders->count())
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Customer</th>
                </tr>
            </thead>

            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="little-dot mr-1 bg-{{ $order->orderStatus->color }}"></div>
                                <a href="{{ $order->updateUrl() }}">Order #{{ $order->id }}</a>
                            </div>
                        </td>

                        <td>
                            {{ $order->created_at->toFormattedDateString() }}
                        </td>

                        <td>
                            <a href="{{ $order->customer->updateUrl() }}">{{ $order->customer->email }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="content p-2">
            <p>No orders exist.</p>
        </div>
    @endif
</div>
