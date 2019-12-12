@extends('statamic::layout')
@section('title', 'Orders')

@section('content')
    <div class="flex mb-3">
        <h1 class="flex-1">Orders</h1>

        <a href="{{ cp_route('orders.create') }}" class="btn btn-primary">Create Order</a>
    </div>

    <commerce-listing
        model="orders"
        cols='{{ json_encode([
            [
                'label' => 'Order ID',
                'field' => 'slug',
            ],
            [
                'label' => 'Total',
                'field' => 'total'
            ]
        ]) }}'
        items='@json($orders)'
        primary='slug'
    />
@endsection
