@extends('statamic::layout')
@section('title', __('Coupons'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ __('Coupons') }}</h1>

        @if(auth()->user()->can('create coupons'))
            <a class="btn-primary" href="{{ cp_route('simple-commerce.coupons.create') }}">{{ __('Create Coupon') }}</a>
        @endif
    </div>

    @if ($couponsCount)
        <coupon-listing
            :filters="{{ $filters->toJson() }}"
            :listing-config='@json($listingConfig)'
            :initial-columns='@json($columns)'
            action-url="{{ $actionUrl }}"
        ></coupon-listing>
    @else
        @include('statamic::partials.create-first', [
            'resource' => __('Coupon'),
            'svg' => 'empty/collection',
            'route' => cp_route('simple-commerce.coupons.create'),
        ])
    @endif
@endsection
