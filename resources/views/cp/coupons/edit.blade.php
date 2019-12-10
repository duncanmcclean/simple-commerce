@extends('statamic::layout')
@section('title', 'Edit Coupon')

@section('content')
    <publish-form
            title="{{ $values['title'] }}"
            action="{{ cp_route('coupons.update', ['coupon' => $values['id']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
