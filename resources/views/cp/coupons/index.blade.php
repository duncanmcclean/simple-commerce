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

    <coupon-listing
        sort-column="code"
        sort-direction="asc"
        :columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ $actionUrl }}"
    ></coupon-listing>

    <x-statamic::docs-callout :topic="__('Coupons')" url="https://simple-commerce.duncanmcclean.com/coupons" />
@endsection
