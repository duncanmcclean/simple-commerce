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
@endsection
