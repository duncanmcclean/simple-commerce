@extends('statamic::layout')
@section('title', __('Coupons'))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ __('Coupons') }}</h1>

        @if(auth()->user()->can('create coupons'))
            <a class="btn-primary" href="{{ cp_route('simple-commerce.coupons.create') }}">{{ __('Create Coupon') }}</a>
        @endif
    </div>

    <coupons-listing
        sort-column="code"
        sort-direction="asc"
        :initial-columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        :action-url="{{ json_encode(cp_route('simple-commerce.coupons.actions.run')) }}"
    ></coupons-listing>
@endsection
