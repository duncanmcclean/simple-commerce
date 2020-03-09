@extends('statamic::layout')
@section('title', 'Edit Customer')
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <publish-form
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        title="{{ $values['name'] }}"
        name="customer-publish-form"
        :breadcrumbs='@json($crumbs)'
        action="{{ $action }}"
        method="post"
        @saved="window.location.reload()"
    ></publish-form>

    <script>window.customerId = '{{ $values['id'] }}';</script>
@endsection
