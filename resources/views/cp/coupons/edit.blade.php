@extends('statamic::layout')
@section('title', 'Edit Coupon')
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <publish-form
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        title="{{ $values['name'] }}"
        name="coupon-publish-form"
        :breadcrumbs='@json($crumbs)'
        action="{{ $action }}"
        method="post"
        @saved="window.location.reload()"
    ></publish-form>
@endsection
