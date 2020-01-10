@extends('statamic::layout')
@section('title', 'Edit Customer')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <publish-form
            title="{{ $values['name'] }}"
            action="{{ cp_route('customers.update', ['customer' => $values['uid']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>

    <script>
        window.customerId = '{{ $values['id'] }}'; // this is needed for the order component
    </script>
@endsection
