@extends('statamic::layout')
@section('title', 'Create Coupon')

@section('content')
    <publish-form
            title="Create Coupon"
            action="{{ cp_route('coupons.store') }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
