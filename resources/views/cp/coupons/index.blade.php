@extends('statamic::layout')
@section('title', __('Coupons'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <ui-header title="{{ __('Coupons') }}" icon="taxonomies">
        @if(auth()->user()->can('create coupons'))
            <ui-button
                href="{{ cp_route('simple-commerce.coupons.create') }}"
                text="{{ __('Create Coupon') }}"
                variant="primary"
            ></ui-button>
        @endif
    </ui-header>

    @if ($couponsCount)
        <coupon-listing
            sort-column="code"
            sort-direction="asc"
            :columns="{{ $columns->toJson() }}"
            :filters="{{ $filters->toJson() }}"
            action-url="{{ $actionUrl }}"
        ></coupon-listing>
    @else
        @include('statamic::partials.empty-state', [
            'title' => __('Coupons'),
            'description' => __('Coupons are a great way to offer discounts to your customers. You can create coupons and apply them to orders.'),
            'svg' => 'empty/content',
            'button_text' => __('Create Coupon'),
            'button_url' => cp_route('simple-commerce.coupons.create'),
            'can' => auth()->user()->can('create coupons'),
        ])
    @endif
@endsection
