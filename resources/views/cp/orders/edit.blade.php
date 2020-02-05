@extends('statamic::layout')
@section('title', 'Edit Order')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <publish-form
            title="Order #{{ $values['id'] }}"
            action="{{ cp_route('orders.update', ['order' => $values['uuid']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
