@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-3xl')

@section('content')

    <base-coupon-create-form
        title="{{ $title }}"
        :actions="{{ json_encode($actions) }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        create-another-url="{{ cp_route('simple-commerce.coupons.create') }}"
        listing-url="{{ cp_route('simple-commerce.coupons.index') }}"
    ></base-coupon-create-form>

@endsection
