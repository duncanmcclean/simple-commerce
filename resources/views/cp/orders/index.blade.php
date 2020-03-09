@extends('statamic::layout')
@section('title', 'Orders')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">Orders</h1>

        <dropdown-list class="mr-1">
            @if(auth()->user()->hasPermission('edit simple commerce settings') || auth()->user()->isSuper())
                <dropdown-item text="Configure Order Statuses" redirect="{{ cp_route('settings.order-statuses.index') }}"></dropdown-item>
            @endif
        </dropdown-list>
    </div>

    @if ($orders->count())
        <div class="card p-0">
            <table class="bg-white data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th class="actions-column"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="little-dot mr-1 bg-{{ $order->orderStatus->color }}"></div>
                                    <a href="{{ $order->editUrl() }}">Order #{{ $order->id }}</a>
                                </div>
                            </td>

                            <td>
                                <a href="{{ $order->customer->editUrl() }}">{{ $order->customer->name }}</a>
                            </td>

                            <td>
                                {{ $order->created_at->toFormattedDateString() }}
                            </td>

                            <td class="flex justify-end">
                                <dropdown-list>
                                    @foreach($statuses as $status)
                                        <dropdown-item text="Set as {{ $status->name }}" redirect="{{ cp_route('orders.status-update', ['order' => $order->uuid, 'status' => $status->uuid]) }}"></dropdown-item>
                                    @endforeach

                                    <div class="divider"></div>

                                    @if(! $order->is_refunded)
                                        @if(auth()->user()->hasPermission('refund orders') || auth()->user()->isSuper())
                                            <dropdown-item text="Refund" redirect="{{ cp_route('commerce-api.refund-order', ['order' => $order->uuid]) }}"></dropdown-item>
                                        @endif
                                    @endif

                                    <dropdown-item text="Edit" redirect="{{ $order->editUrl() }}"></dropdown-item>
                                    <dropdown-item class="warning" text="Delete" redirect="{{ $order->deleteUrl() }}"></dropdown-item>
                                </dropdown-list>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($orders->hasMorePages())
                <div class="w-full flex mt-3">
                    <div class="flex-1"></div>

                    <ul class="flex justify-center items-center list-reset">
                        @if($orders->previousPageUrl())
                            <li class="mx-1">
                                <a href="{{ $orders->previousPageUrl() }}"><span>&laquo;</span></a>
                            </li>
                        @endif

                        @foreach($orders->links()->elements[0] as $number => $link)
                            <li class="mx-1 @if($number === $orders->currentPage()) font-bold @endif">
                                <a href="{{ $link }}">{{ $number }}</a>
                            </li>
                        @endforeach

                        @if($orders->nextPageUrl())
                            <li class="mx-1">
                                <a href="{{ $orders->nextPageUrl() }}">
                                    <span>»</span>
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="flex flex-1">
                        <div class="flex-1"></div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <p>No one has purchased from your store. When they do, orders will be displayed here.</p>
    @endif
@endsection
