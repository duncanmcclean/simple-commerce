@extends('statamic::layout')
@section('title', 'Orders')

@section('content')
    <div class="flex items-center justify-between mb-3">
        @if(request()->has('view-carts'))
            <h1 class="flex-1">Carts</h1>
        @elseif(request()->has('status'))
            <h1 class="flex-1">Orders in {{ $status->name }}</h1>
        @else
            <h1 class="flex-1">Orders</h1>
        @endif

        @if($orders->count())
            <dropdown-list class="mr-1">
                @if(auth()->user()->hasPermission('edit simple commerce settings') || auth()->user()->isSuper())
                    <dropdown-item text="Configure Order Statuses" redirect="{{ cp_route('settings.order-statuses.index') }}"></dropdown-item>
                @endif
            </dropdown-list>
        @endif
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

                            <td><a href="{{ $order->customer->editUrl() }}">{{ $order->customer->name }}</a></td>
                            <td>{{ $order->created_at->toFormattedDateString() }}</td>
                            <td class="flex justify-end">
                                <simple-commerce-actions>
                                    @foreach($statuses as $status)
                                        <simple-commerce-action-item
                                            type="standard"
                                            text="Set as {{ $status->name }}"
                                            action="{{ cp_route('orders.status', ['order' => $order->uuid, 'status' => $status->uuid]) }}"
                                        ></simple-commerce-action-item>
                                    @endforeach

                                    <div class="divider"></div>

{{--                                    @if(! $order->is_refunded)--}}
{{--                                        @if (auth()->user()->hasPermission('refund orders') || auth()->user()->isSuper())--}}
{{--                                            <simple-commerce-action-item--}}
{{--                                                type="standard"--}}
{{--                                                text="Refund"--}}
{{--                                                action="{{ cp_route('orders.refund', ['order' => $order->uuid]) }}"--}}
{{--                                            ></simple-commerce-action-item>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}

                                    <simple-commerce-action-item
                                        type="standard"
                                        text="Edit"
                                        action="{{ $order->editUrl() }}"
                                    ></simple-commerce-action-item>
                                    <simple-commerce-action-item
                                        type="delete"
                                        text="Delete"
                                        action="{{ $order->deleteUrl() }}"
                                        method="delete"
                                        modal-title="Delete Order"
                                        modal-text="Are you sure you want to delete this order?"
                                    ></simple-commerce-action-item>
                                </simple-commerce-actions>
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
        <div class="card p-4">
            <p>There's nothing to show, your store doesn't have any orders. <a href="/" target="_blank" class="text-blue hover:text-blue-dark">Buy something</a>.</p>
        </div>
    @endif
@endsection
