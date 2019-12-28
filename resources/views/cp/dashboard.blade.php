@extends('statamic::layout')
@section('title', __('Dashboard'))

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex mb-3">
        <h1 class="flex-1">Dashboard</h1>
    </div>

    <div class="widgets flex flex-wrap -mx-2 py-1">
        <div class="widget w-full  mb-4 px-2">
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
        </div>
    </div>

    <div class="widgets flex flex-wrap -mx-2 py-1">
        <div class="widget w-full  mb-4 px-2">
            <div class="card p-0 content">
                <div class="p-3">
                    <h1>Recent Customers</h1>

                    <div class="flex flex-col">
                        @foreach($customers as $customer)
                            <div class="flex flex-row items-center justify-between py-2">
                                <a href="{{ cp_route('customers.edit', ['customer' => $customer['id']]) }}">{{ $customer['name'] }}</a>
                                <span>{{ \Carbon\Carbon::parse($customer['customer_since'])->toFormattedDateString() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
