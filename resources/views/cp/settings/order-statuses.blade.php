@extends('statamic::layout')
@section('title', 'Order Statuses')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="content">
        <h1 class="mb">Order Statuses</h1>
    </div>

    <order-status-settings
        index-endpoint="{{ cp_route('commerce-api.order-status.index') }}"
        store-endpoint="{{ cp_route('commerce-api.order-status.store') }}"
        initial-blueprint='@json($blueprint)'
        initial-meta='@json($meta)'
        initial-values='@json($values)'
    ></order-status-settings>
@endsection
