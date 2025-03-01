@extends('statamic::layout')
@section('title', __('Orders'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ __('Orders') }}</h1>

        <dropdown-list class="rtl:ml-2 ltr:mr-2">
            @can('configure fields')
                <dropdown-item :text="__('Edit Blueprint')" redirect="{{ cp_route('blueprints.edit', ['simple-commerce', 'order']) }}"></dropdown-item>
            @endcan
        </dropdown-list>
    </div>

    <orders-listing
        sort-column="order_number"
        sort-direction="desc"
        :initial-columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        :action-url="{{ json_encode(cp_route('simple-commerce.orders.actions.run')) }}"
    ></orders-listing>
@endsection
