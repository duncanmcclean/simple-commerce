@extends('statamic::layout')
@section('title', 'Orders')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            Orders
        </h1>
    </div>

    @if ($orders->count())
        <table class="bg-white data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Customer</th>
                    <th></th>
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
                            {{ $order->created_at->toFormattedDateString() }}
                        </td>

                        <td>
                            <a href="{{ $order->customer->editUrl() }}">{{ $order->customer->name }}</a>
                        </td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                @if(auth()->user()->hasPermission('refund orders') || auth()->user()->isSuper())
                                    <dropdown-item text="Refund" redirect="{{ cp_route('commerce-api.refund-order', ['order' => $order->uid]) }}"></dropdown-item>
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
    @else
        @component('statamic::partials.create-first', [
            'resource' => 'Order',
            'svg' => 'empty/collection',
        ])
            <a
                    class="btn btn-primary"
                    href="{{ $createUrl }}"
            >
                Create Order
            </a>
        @endcomponent
    @endif
@endsection
