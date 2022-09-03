@extends('statamic::layout')
@section('title', 'Edit Coupon')
@section('wrapper_class', 'max-w-3xl')

@section('content')
    @include('simple-commerce::cp.partials.breadcrumbs', [
        'title' => __('Coupons'),
        'url' => cp_route('simple-commerce.coupons.index'),
    ])

    <publish-form
        title="Edit Coupon"
        action="{{ $coupon->updateUrl() }}"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
    ></publish-form>
@endsection
