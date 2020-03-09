@extends('statamic::layout')
@section('title', 'Edit Customer')
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <publish-form
            title="{{ $values['name'] }}"
            action="{{ cp_route('customers.update', ['customer' => $values['uuid']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>

    <script>
        window.customerId = '{{ $values['id'] }}'; // this is needed for the order component
    </script>
@endsection
