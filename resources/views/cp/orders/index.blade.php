@extends('statamic::layout')
@section('title', 'Orders')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            Orders
        </h1>

        <a class="btn btn-primary" href="{{ $createUrl }}">
            Create Order
        </a>
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
                                <dropdown-item text="Edit" redirect="{{ $order->editUrl() }}"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" redirect="{{ $order->deleteUrl() }}"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
