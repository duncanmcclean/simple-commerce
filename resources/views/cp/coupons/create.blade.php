@extends('statamic::layout')
@section('title', __('Create Coupon'))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    @include('simple-commerce::cp.partials.breadcrumbs', [
        'title' => __('Coupons'),
        'url' => cp_route('simple-commerce.coupons.index'),
    ])

    <publish-form
        title="{{ __('Create Coupon') }}"
        action="{{ cp_route('simple-commerce.coupons.store') }}"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        @saved="window.location.href = $event.data.redirect"
    ></publish-form>
@endsection
